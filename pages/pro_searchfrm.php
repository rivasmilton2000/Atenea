<?php
include '../includes/connection.php';

include '../includes/sidebar.php';

$query = 'SELECT ID, t.TYPE
        FROM users u
        JOIN type t ON t.TYPE_ID = u.TYPE_ID WHERE ID = ' . $_SESSION['MEMBER_ID'];
$result = mysqli_query($db, $query) or die(mysqli_error($db));

while ($row = mysqli_fetch_assoc($result)) {
    $Aa = $row['TYPE'];

    if ($Aa == 'User') {
        ?>
        <script type="text/javascript">
            // Redirects users to the POS page if they're categorized as 'User'
            alert("Página restringida! Será redirigido a POS");
            window.location = "pos.php";
        </script>
        <?php
    }
}
?>

<center>
    <div class="card shadow mb-4 col-xs-12 col-md-8 border-bottom-primary">
        <div class="card-header py-3">
            <h4 class="m-2 font-weight-bold text-primary">Detalles del producto</h4>
        </div>
        <a href="product.php?action=add" type="button" class="btn btn-primary bg-gradient-primary btn-block">
            <i class="fas fa-flip-horizontal fa-fw fa-share"></i> Regresar
        </a>
        <div class="card-body">
            <?php
            $query = 'SELECT PRODUCT_ID, PRODUCT_CODE, NAME,DESCRIPTION, COUNT(`QTY_STOCK`) AS "QTY_STOCK", COUNT(`ON_HAND`) AS "ON_HAND", c.CNAME, DATE_STOCK_IN FROM product p join category c on p.CATEGORY_ID=c.CATEGORY_ID WHERE PRODUCT_CODE =' . $_GET['id'];
            $result = mysqli_query($db, $query) or die(mysqli_error($db));
            while ($row = mysqli_fetch_array($result)) {
                $zz = $row['PRODUCT_ID'];
                $zzz = $row['PRODUCT_CODE'];
                $i = $row['NAME'];
                $a = $row['DESCRIPTION'];
                $qty = $row['QTY_STOCK'];
                $onHand = $row['ON_HAND'];
                $d = $row['CNAME'];
                $dateInStock = $row['DATE_STOCK_IN'];
            }
            $id = $_GET['id'];
            ?>

            <!-- Displaying product details -->
            <div class="form-group row text-left">
                <div class="col-sm-3 text-primary">
                    <h5>Código del producto<br></h5>
                </div>
                <div class="col-sm-9">
                    <h5>: <?php echo $zzz; ?><br></h5>
                </div>
            </div>
            <div class="form-group row text-left">
                <div class="col-sm-3 text-primary">
                    <h5>Nombre del producto<br></h5>
                </div>
                <div class="col-sm-9">
                    <h5>: <?php echo $i; ?><br></h5>
                </div>
            </div>
            <div class="form-group row text-left">
                <div class="col-sm-3 text-primary">
                    <h5>Cantidad<br></h5>
                </div>
                <div class="col-sm-9">
                    <h5>: <?php echo $qty; ?><br></h5>
                </div>
            </div>
            <div class="form-group row text-left">
                <div class="col-sm-3 text-primary">
                    <h5>Disponible<br></h5>
                </div>
                <div class="col-sm-9">
                    <h5>: <?php echo $onHand; ?><br></h5>
                </div>
            </div>
            <div class="form-group row text-left">
                <div class="col-sm-3 text-primary">
                    <h5>Fecha de entrada de existencias<br></h5>
                </div>
                <div class="col-sm-9">
                    <h5>: <?php echo $dateInStock; ?><br></h5>
                </div>
            </div>

        </div>
    </div>
</center>



<div class="card shadow mb-4 col-xs-12 col-md-15 border-bottom-primary">
    <div class="card-header py-3">
        <h4 class="m-2 font-weight-bold text-primary">Más inventario</h4>
    </div>
    <div class="card-body">
        <div class="table-responsive">
            <table class="table table-bordered" id="dataTable" width="100%" cellspacing="0">
                <!-- ... (existing code remains unchanged) ... -->
                <thead>
                    <tr>
                        <th>Código del producto</th>
                        <th>Nombre del producto</th>
                        <th>Cantidad</th>
                        <th>Disponible</th>
                        <th>Categoria</th>
                        <th>Fecha de entrada de existencias</th>
                    </tr>
                </thead>
                <tbody>
                    <?php
                    $query = 'SELECT PRODUCT_ID, PRODUCT_CODE, NAME, COUNT("QTY_STOCK") AS QTY_STOCK, COUNT("ON_HAND") AS ON_HAND, CNAME, DATE_STOCK_IN FROM product p join category c on p.CATEGORY_ID=c.CATEGORY_ID WHERE PRODUCT_CODE =' . $zzz . ' GROUP BY `DATE_STOCK_IN`';
                    $result = mysqli_query($db, $query) or die(mysqli_error($db));

                    while ($row = mysqli_fetch_assoc($result)) {
                        echo '<tr>';
                        echo '<td>' . $row['PRODUCT_CODE'] . '</td>';
                        echo '<td>' . $row['NAME'] . '</td>';
                        echo '<td>' . $row['QTY_STOCK'] . '</td>';
                        echo '<td>' . $row['ON_HAND'] . '</td>';
                        echo '<td>' . $row['CNAME'] . '</td>';
                        echo '<td>' . $row['DATE_STOCK_IN'] . '</td>';
                        echo '</tr> ';
                    }
                    ?>
                </tbody>
            </table>
        </div>
    </div>
</div>

<?php
include '../includes/footer.php';
?>
