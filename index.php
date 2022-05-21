<?php

use PhpOffice\PhpSpreadsheet\IOFactory;

require_once 'init.php';
require 'vendor/autoload.php';

$err = '';
$success = '';

if (isset($_POST['upload-action'])) {

    $fname = $_FILES['upload']['name'];
    $ftemp = $_FILES['upload']['tmp_name'];
    // Builtin PHP func.
    // Pathinfo : basename, extension dan filename 
    // pathinfo(file_yang_diupload);



    if (empty(pathinfo($fname)['basename'])) {
        $err = "Silahkan pilih file terlebih dahulu";
    } else {

        // Batasi ekstensi
        $ext = ['xls', 'xlsx'];
        if (!in_array(pathinfo($fname)['extension'], $ext)) {
            // $err = 'Ekstensi tidak diizinkan';
            $err = 'Ekstensi tidak diizinkan';
            echo "<script>alert('Ekstensi tidak diizinkan');location.href='index.php';</script>";
        }

        // Ubah nama file dan validasi
        $fname_new = strtolower($fname);
        $fname_new = 'temp_' . $fname_new;

        // UPLOAD FILE KE TEMPORARY FILE
        $dir_temp = '_temp/';
        $full_path = $dir_temp . $fname_new;
        // Buat dir dan permissions
        if (!is_dir($dir_temp)) {
            mkdir($dir_temp, 0777);
        }


        $reader = \PhpOffice\PhpSpreadsheet\IOFactory::createReaderForFile($ftemp);
        $spreadsheet = $reader->load($ftemp);
        try {
            $spreadsheet->setActiveSheetIndexByName('IMPORT');
            $sheetActive = $spreadsheet->getActiveSheet()->toArray();
        } catch (Throwable $e) {
            $err = 'Sheet IMPORT Tidak Ditemukan';
            echo "<script>alert('Sheet1 Tidak Ditemukan');location.href='index.php';</script>";
            exit();
        }

        // upload file
        move_uploaded_file($ftemp, $full_path);

        if (!is_file($full_path)) {
            $err = 'File tidak berhasil diupload';
        } else {
            $success = 'File Berhasil diupload';
        }


        $jmlData = 0;
        $result = [];
        for ($i = 1; $i < count($sheetActive); $i++) {
            $uniq = preg_replace("/[^0-9]/", "", $sheetActive[$i][7]);
            $fNama = $sheetActive[$i][1];
            $lNama = $sheetActive[$i][2];
            $gender = $sheetActive[$i][3];
            $country = $sheetActive[$i][4];
            $age = preg_replace("/[^0-9]/", "", $sheetActive[$i][5]);

            // date
            $d = explode('/', $sheetActive[$i][6]);
            $thn = $d[2];
            $bln = $d[1];
            $tgl = $d[0];
            $date = $thn . '-' . $bln . '-' . $tgl;

            $result[] = $sheetActive;
            $jmlData++;
        }
    }


    // Jika tidak ada error
    if (empty($err)) {
        $success = $jmlData . " Data ditemukan";
    }
}

if (isset($_POST['batal'])) {
    unlink('_temp/' . $_POST['filename']);
    header('Location:index.php');
}

if (isset($_POST['simpan'])) {

    $file = $_POST['filename'];
    $full_path = '_temp/' . $file;
    if (!is_file($full_path)) {
        $err = 'Sheet IMPORT Tidak Ditemukan';
        echo "<script>alert('Sheet1 Tidak Ditemukan');location.href='index.php';</script>";
        exit();
    }

    $reader = \PhpOffice\PhpSpreadsheet\IOFactory::createReaderForFile($full_path);
    $spreadsheet = $reader->load($full_path);

    try {
        $spreadsheet->setActiveSheetIndexByName('IMPORT');
        $sheetActive = $spreadsheet->getActiveSheet()->toArray();
    } catch (Throwable $e) {
        $err = 'Sheet IMPORT Tidak Ditemukan';
        echo "<script>alert('Sheet1 Tidak Ditemukan');location.href='index.php';</script>";
        exit();
    }

    $jmlData = 0;
    $result = [];
    for ($i = 1; $i < count($sheetActive); $i++) {
        $uniq = preg_replace("/[^0-9]/", "", $sheetActive[$i][7]);
        $fNama = $sheetActive[$i][1];
        $lNama = $sheetActive[$i][2];
        $gender = $sheetActive[$i][3];
        $country = $sheetActive[$i][4];
        $age = preg_replace("/[^0-9]/", "", $sheetActive[$i][5]);

        // date
        $d = explode('/', $sheetActive[$i][6]);
        $thn = $d[2];
        $bln = $d[1];
        $tgl = $d[0];
        $date = $thn . '-' . $bln . '-' . $tgl;

        $result[] = $sheetActive;
        if (!empty($uniq) && !empty($fNama) && !empty($lNama) && !empty($gender)) {
            $qu = "INSERT INTO data(id,uniq,fname,lname,gender,country,age,date) VALUES('','$uniq','$fNama','$lNama','$gender','$country','$age','$date')";

            mysqli_query($conn, $qu);

            $jmlData++;
        }

        if ($jmlData > 0 && is_file($full_path)) {
            unlink('./' . $full_path);
        }


        // Jika tidak ada error
        if (empty($err)) {
            $success = $jmlData . " Data berhasil tersimpan";
            $result = [];
        }
    }
}

?>
<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Import Excel dengan PHPSpreadsheet</title>
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/css/bootstrap.min.css">
</head>

<body>

    <div class="container">
        <div class="row d-flex flex-column justify-content-center align-items-center">
            <div class="col-xl-8 text-center py-5">
                <?php
                if (!empty($err)) {
                    echo "<p class='text-danger text-center'>" . $err . "</p>";
                    echo "<br>";
                } else {
                    echo "<p class='text-success text-center'>" . $success . "</p>";
                }
                ?>
                <form action="" method="post" enctype="multipart/form-data">
                    <div class="form-group my-2">
                        <label for="upload" class="my-3 fw-bold">Pilih File xls/xlxs</label>
                        <input type="file" name="upload" id="upload" class="form-control">
                    </div>
                    <div class="form-group my-2">
                        <button name="upload-action" class="btn btn-primary text-center">Upload Excel</button>
                        <a href="./_files/template.xlsx" class="btn btn-outline-secondary text-center">Download Template</a>
                    </div>
                </form>
            </div>
        </div>

        <!-- result -->
        <div class="row">
            <div class="col-xl-12 p-5 border">
                <h4 class="text-center text-muted mb-4">Preview Data</h4>

                <?php
                if (!empty($result)) :
                ?>
                    <table class="table">
                        <thead>
                            <tr>
                                <th>No.</th>
                                <th>ID</th>
                                <th>First Name</th>
                                <th>Last Name</th>
                                <th>Gender</th>
                                <th>Country</th>
                                <th>Age</th>
                                <th>Date</th>
                            </tr>
                        </thead>
                        <tbody>

                            <?php
                            for ($i = 1; $i < count($result[0]); $i++) :
                                $uniq = $result[0][$i][7];
                                $fNama = $result[0][$i][1];
                                $lNama = $result[0][$i][2];
                                $gender = $result[0][$i][3];
                                $country = $result[0][$i][4];
                                $age = $result[0][$i][5];

                                // date
                                $d = explode('/', $result[0][$i][6]);
                                $thn = $d[2];
                                $bln = $d[1];
                                $tgl = $d[0];
                                $date = $thn . '-' . $bln . '-' . $tgl;
                            ?>
                                <tr>
                                    <td><?= $i; ?></td>
                                    <td><?= $uniq; ?></td>
                                    <td><?= $fNama; ?></td>
                                    <td><?= $lNama; ?></td>
                                    <td><?= $gender; ?></td>
                                    <td><?= $country; ?></td>
                                    <td><?= $age; ?></td>
                                    <td><?= $date; ?></td>
                                </tr>
                        <?php
                            endfor;
                        endif;
                        ?>
                        </tbody>
                    </table>

                    <?php
                    if (!empty($result)) :
                    ?>
                        <form action="" method="post">
                            <div class="btn-process d-flex flex-column">

                                <input type="hidden" name="filename" value="<?= isset($fname_new) ? $fname_new : ''; ?>">
                                <button name="simpan" type="submit" class="btn btn-success text-center">Lanjutkan Import</button>
                                <button name="batal" type="submit" class="btn text-center text-danger">Batalkan</button>


                            </div>
                        </form>
                    <?php
                    endif;
                    ?>

            </div>
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/js/bootstrap.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/js/bootstrap.bundle.min.js"></script>
</body>

</html>