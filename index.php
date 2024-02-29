<?php

session_start();

$servername = "localhost";
$username = "root";
$password = "";
$dbname = "prg_test";

// Membuat koneksi
$conn = new mysqli($servername, $username, $password, $dbname);

// Memeriksa koneksi
if ($conn->connect_error) {
    die("Koneksi gagal: " . $conn->connect_error);
}

$success = null;
$danger = null;

$request_method = strtoupper($_SERVER['REQUEST_METHOD']);

if ($request_method === 'POST') {

    // Get the requested URL
    $requestUri = $_SERVER['REQUEST_URI'];

    // Remove any query string from the URL
    $requestUri = strtok($requestUri, '?');

    // Split the URL into segments
    $segments = explode('/', trim($requestUri, '/'));

    // Handle requests to index.php
    if (isset($segments[2]) && $segments[2] === 'customers' && isset($segments[3]) && $segments[4] == 'update') {
        $company = $_POST["company"];
        $contact = $_POST["contact"];
        $country = $_POST["country"];

        // Menyusun SQL query untuk UPDATE
        $sql = "UPDATE customers SET Company='$company', Contact='$contact', Country='$country' WHERE id=$segments[3]";

        // Menjalankan query
        if ($conn->query($sql) === TRUE) {
            // echo "Data berhasil disimpan";
            $_SESSION['success'] = "Data berhasil diperbarui";
        } else {
            // echo "Error: " . $sql . "<br>" . $conn->error;
            $_SESSION['danger'] = "Error: " . $sql . "<br>" . $conn->error;
        }

        header("Location: ../../../index.php", true, 303);
        exit;
    } elseif (isset($segments[2]) && $segments[2] === 'customers' && isset($segments[3]) && $segments[4] == 'delete') {
        // Menyusun SQL query untuk DELETE berdasarkan ID
        $sql = "DELETE FROM customers WHERE id = $segments[3]";

        // Menjalankan query
        if ($conn->query($sql) === TRUE) {
            // echo "Data berhasil disimpan";
            $_SESSION['success'] = "Data berhasil dihapus";
        } else {
            // echo "Error: " . $sql . "<br>" . $conn->error;
            $_SESSION['danger'] = "Error: " . $sql . "<br>" . $conn->error;
        }

        header("Location: ../../../index.php", true, 303);
        exit;
    }

    $company = $_POST["company"];
    $contact = $_POST["contact"];
    $country = $_POST["country"];

    // Menyusun SQL query untuk INSERT
    $sql = "INSERT INTO customers (Company, Contact, Country) VALUES ('$company', '$contact', '$country')";

    // Menjalankan query
    if ($conn->query($sql) === TRUE) {
        // echo "Data berhasil disimpan";
        $_SESSION['success'] = "Data berhasil disimpan";
    } else {
        // echo "Error: " . $sql . "<br>" . $conn->error;
        $_SESSION['danger'] = "Error: " . $sql . "<br>" . $conn->error;
    }

    // redirect to the page itself
    header("Location: index.php", true, 303);
    exit;
} elseif ($request_method === 'GET') {

    // Get the requested URL
    $requestUri = $_SERVER['REQUEST_URI'];

    // Remove any query string from the URL
    $requestUri = strtok($requestUri, '?');

    // Split the URL into segments
    $segments = explode('/', trim($requestUri, '/'));

    // Handle requests to index.php
    if (isset($segments[2]) && $segments[2] === 'customers' && isset($segments[3])) {

        $sql = "SELECT * FROM customers WHERE id = $segments[3]";

        // Menjalankan query
        $result = $conn->query($sql);

        // Memeriksa hasil query
        if ($result->num_rows > 0) {
            // Mengambil data pelanggan
            $customerData = $result->fetch_assoc();

            echo json_encode($customerData);
        } else {
            http_response_code(404);
            echo json_encode(array("message" => "Data not found"));
        }
        exit;
    }

    $company = '';
    $contact = '';
    $country = '';

    $filter = "";
    if (isset($_GET['ficompany']) && $_GET['ficompany'] !== '') {
        $company = $_GET['ficompany'];
        $filter .= "and company like '%$company%' ";
    }
    if (isset($_GET['ficontact']) && $_GET['ficontact'] !== '') {
        $contact = $_GET['ficontact'];
        $filter .= "and contact like '%$contact%' ";
    }
    if (isset($_GET['ficountry']) && $_GET['ficountry'] !== '') {
        $country = $_GET['ficountry'];
        $filter .= "and country like '%$country%' ";
    }

    // Menjalankan query untuk mendapatkan data dari tabel "customers"
    $sql = "SELECT * FROM customers where 1=1 $filter";

    $result = $conn->query($sql);

    // Membuat array untuk menampung data
    $data = array();

    // Mengekstrak hasil query dan memasukkannya ke dalam array
    if ($result->num_rows > 0) {
        while ($row = $result->fetch_assoc()) {
            $data[] = (object)$row;
        }
    }

    // Menutup koneksi database
    $conn->close();

    if (isset($_SESSION['success'])) {
        $success = $_SESSION['success'];
        unset($_SESSION['success']);
    }

    if (isset($_SESSION['danger'])) {
        $danger = $_SESSION['danger'];
        unset($_SESSION['danger']);
    }
}
?>
<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link rel="stylesheet" href="css/style.css">
    <title>PHP PRG</title>
</head>

<body>
    <?php if ($success) { ?>
        <div class="alert alert-success">
            <span class="close-button" onclick="closeAlert()">&times;</span>
            <p>Success: <?= $success ?></p>
        </div>
    <?php } ?>
    <?php if ($danger) { ?>
        <div class="alert alert-danger">
            <span class="close-button" onclick="closeAlert()">&times;</span>
            <p>Error: <?= $danger ?></p>
        </div>
    <?php } ?>
    <h2>HTML Table</h2>

    <a href="#" class="button" onclick="openModal(this,'Add')">Add</a>
    <a href="#" class="button button-gray" onclick="resetFilter()">Reset Filter</a>
    <table>
        <tr>
            <th>Company</th>
            <th>Contact</th>
            <th>Country</th>
            <th></th>
        </tr>
        <tr>
            <form action="index.php" id="ffilter">
                <td><input type="text" name="ficompany" onkeydown="handleEnterKey(event)" value="<?= $company ?>"></td>
                <td><input type="text" name="ficontact" onkeydown="handleEnterKey(event)" value="<?= $contact ?>"></td>
                <td><input type="text" name="ficountry" onkeydown="handleEnterKey(event)" value="<?= $country ?>"></td>
            </form>
        </tr>
        <?php
        if ($data) {
            foreach ($data as $row) { ?>
                <tr>
                    <td><?= $row->Company ?></td>
                    <td><?= $row->Contact ?></td>
                    <td><?= $row->Country ?></td>
                    <td><a href="#" data-id="<?= $row->id ?>" onclick="openModal(this,'Edit')">Edit</a> | <a href="#" data-id="<?= $row->id ?>" onclick="openModal(this,'Delete')">Delete</a></td>
                </tr>
        <?php }
        } ?>
    </table>

    <!-- Overlay dan Kontainer Modal -->
    <div class="overlay" id="overlayAdd" onclick="closeModal('Add')"></div>
    <div class="modal" id="modalAdd">
        <span class="close" onclick="closeModal('Add')">&times;</span>
        <h2>Add</h2>
        <form action="index.php" method="POST" id="fAdd">
            <label for="company">Company:</label><br>
            <input type="text" name="company"><br>
            <label for="contact">Contact:</label><br>
            <input type="text" name="contact"><br>
            <label for="country">Country:</label><br>
            <input type="text" name="country"><br><br>
            <input type="submit" value="Submit">
        </form>
    </div>

    <!-- Overlay dan Kontainer Modal -->
    <div class="overlay" id="overlayEdit" onclick="closeModal('Edit')"></div>
    <div class="modal" id="modalEdit">
        <span class="close" onclick="closeModal('Edit')">&times;</span>
        <h2>Edit</h2>
        <form action="" method="POST" id="fEdit">
            <input type="hidden" name="id">
            <label for="company">Company:</label><br>
            <input type="text" name="company"><br>
            <label for="contact">Contact:</label><br>
            <input type="text" name="contact"><br>
            <label for="country">Country:</label><br>
            <input type="text" name="country"><br><br>
            <input type="submit" value="Submit">
        </form>
    </div>

    <!-- Overlay and Delete Confirmation Modal -->
    <div class="overlay" id="overlayDelete" onclick="closeModal('Delete')"></div>
    <div class="confirmation-modal" id="modalDelete">
        <form action="" method="POST" id="fDelete">
            <h2>Delete Confirmation</h2>
            <p>Are you sure you want to delete this item?</p>
            <div class="confirmation-buttons">
                <button type="submit" value="Submit">Delete</button>
                <button onclick="closeModal('Delete')">Cancel</button>
            </div>
        </form>
    </div>

    <script>
        function handleEnterKey(event) {
            if (event.key === 'Enter') {
                // Prevent the default form submission
                event.preventDefault();

                // Trigger the form submission
                document.getElementById('ffilter').submit();
            }
        }


        // Fungsi untuk membuka modal
        function openModal(ref, actionString) {
            if (actionString == 'Edit') {
                performHttpGetCustomer(ref.getAttribute('data-id'))
            }

            if (actionString == 'Delete') {
                var id = ref.getAttribute('data-id')
                document.querySelector('#fDelete').action = `index.php/customers/${id}/delete`;
            }

            document.getElementById(`overlay${actionString}`).style.display = "block";
            document.getElementById(`modal${actionString}`).style.display = "block";
        }

        // Fungsi untuk menutup modal
        function closeModal(actionString) {
            document.getElementById(`overlay${actionString}`).style.display = "none";
            document.getElementById(`modal${actionString}`).style.display = "none";
        }

        function closeAlert() {
            var alert = document.querySelector('.success');
            alert.style.display = 'none';
        }

        function performHttpGetCustomer(id) {
            // Create a new XMLHttpRequest object
            var xhr = new XMLHttpRequest();

            // Configure it to make a GET request to a specific URL
            xhr.open("GET", `index.php/customers/${id}`, true);

            // Define a function to handle the response
            xhr.onreadystatechange = function() {
                if (xhr.readyState == 4 && xhr.status == 200) {
                    // Handle the response data here
                    var data = JSON.parse(xhr.response);

                    // Now you can work with the parsed JSON data
                    console.log(data);

                    var form = document.getElementById('fEdit');
                    form.action = `index.php/customers/${data['id']}/update`
                    form.elements['id'].value = data['id']
                    form.elements['company'].value = data['Company']
                    form.elements['contact'].value = data['Contact']
                    form.elements['country'].value = data['Country']
                }
            };

            // Send the request
            xhr.send();
        }

        function resetFilter() {
            // Get the form element by its ID
            var form = document.getElementById("ffilter");
            form.elements['ficompany'].value = ''
            form.elements['ficontact'].value = ''
            form.elements['ficountry'].value = ''
            form.submit()
        }
    </script>
</body>

</html>