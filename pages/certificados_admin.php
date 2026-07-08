<?php
include '../includes/connection.php';
require_once '../includes/atenea_catalog.php';
require_once '../includes/certificate_renderer.php';
include '../includes/sidebar_admin.php';

$query = 'SELECT ID, t.TYPE FROM users u JOIN type t ON t.TYPE_ID=u.TYPE_ID WHERE ID = ' . $_SESSION['MEMBER_ID'] . '';
$result = mysqli_query($db, $query) or die(mysqli_error($db));
while ($row = mysqli_fetch_assoc($result)) {
    $Aa = $row['TYPE'];
    if ($Aa == 'Personal' || $Aa == 'Estudiante' || $Aa == 'Docente' || $Aa == 'SuperAdmin') {
        if ($Aa == 'Personal') {
            $redirectUrl = "empleados_vista.php";
        } elseif ($Aa == 'Estudiante') {
            $redirectUrl = "estudiante_vista.php";
        } elseif ($Aa == 'Docente') {
            $redirectUrl = "docentes_vista.php";
        } else {
            $redirectUrl = "sa_vista.php";
        }
        ?>
        <script type="text/javascript">
            alert("Página restringida! Será redirigido.");
            window.location = "<?php echo $redirectUrl; ?>";
        </script>
        <?php
        exit();
    }
}

$catalogSchema = atenea_catalog_product_schema_flags($db);
$offerOptions = [];
$selectedProductId = (int) ($_POST['producto_id'] ?? $_GET['producto_id'] ?? 0);

if ($catalogSchema['tipo_oferta']) {
    $queryOffers = "
        SELECT id, nombre, descripcion_corta, tipo_oferta
        FROM productos
        WHERE estado = 1
          AND tipo_oferta IN ('curso', 'certificacion')
        ORDER BY tipo_oferta ASC, nombre ASC
    ";
    $resultOffers = mysqli_query($db, $queryOffers);
    if ($resultOffers instanceof mysqli_result) {
        while ($offer = mysqli_fetch_assoc($resultOffers)) {
            $offerOptions[] = $offer;
        }
    }
}

$productPrefill = [];
if ($selectedProductId > 0 && $catalogSchema['tipo_oferta']) {
    $stmtOffer = $db->prepare("
        SELECT id, nombre, descripcion_corta
        FROM productos
        WHERE id = ?
          AND estado = 1
          AND tipo_oferta IN ('curso', 'certificacion')
        LIMIT 1
    ");
    if ($stmtOffer) {
        $stmtOffer->bind_param('i', $selectedProductId);
        $stmtOffer->execute();
        $selectedOffer = $stmtOffer->get_result()->fetch_assoc();
        $stmtOffer->close();

        if (is_array($selectedOffer)) {
            $productPrefill['certificate_name'] = (string) ($selectedOffer['nombre'] ?? '');
            if (trim((string) ($selectedOffer['descripcion_corta'] ?? '')) !== '') {
                $productPrefill['certificate_subtitle'] = (string) $selectedOffer['descripcion_corta'];
            }
        }
    }
}

$input = $_SERVER['REQUEST_METHOD'] === 'POST' ? $_POST : $_GET;
$certificateData = atenea_certificate_build_data(array_merge($productPrefill, is_array($input) ? $input : []));
$pdfUrl = 'certificado_pdf.php?' . http_build_query(atenea_certificate_query_data($certificateData));
?>

<style>
<?php echo atenea_certificate_preview_css(); ?>

.atenea-certificate-admin-grid {
    display: grid;
    grid-template-columns: minmax(320px, 420px) minmax(0, 1fr);
    gap: 1.5rem;
}

.atenea-certificate-admin-grid .card {
    border: 0;
    box-shadow: 0 18px 45px rgba(15, 23, 42, 0.08);
}

.atenea-certificate-note {
    background: #f4faf6;
    border: 1px solid #d6eadb;
    border-radius: 0.9rem;
    color: #486353;
    padding: 0.9rem 1rem;
}

@media (max-width: 1199.98px) {
    .atenea-certificate-admin-grid {
        grid-template-columns: 1fr;
    }
}
</style>

<div class="card shadow mb-4">
    <div class="card-header py-3 d-flex flex-wrap justify-content-between align-items-center">
        <h4 class="m-0 font-weight-bold text-primary">Certificados Atenea</h4>
        <a href="<?php echo htmlspecialchars($pdfUrl, ENT_QUOTES, 'UTF-8'); ?>" target="_blank" class="btn btn-primary">
            <i class="fas fa-file-pdf"></i> Descargar PDF
        </a>
    </div>
    <div class="card-body">
        <p class="mb-3">Genera certificados con datos dinámicos y una plantilla reconstruida sobre la referencia visual original, respetando cinta superior, marcos ornamentales y composición clásica.</p>
        <div class="atenea-certificate-admin-grid">
            <div class="card">
                <div class="card-body">
                    <form method="post">
                        <?php if ($offerOptions !== []) : ?>
                            <div class="form-group">
                                <label><strong>Curso o certificación</strong> (opcional)</label>
                                <select name="producto_id" class="form-control">
                                    <option value="0">Seleccionar manualmente</option>
                                    <?php foreach ($offerOptions as $offerOption) : ?>
                                        <?php $optionType = atenea_catalog_type_label((string) ($offerOption['tipo_oferta'] ?? 'producto')); ?>
                                        <option value="<?php echo (int) $offerOption['id']; ?>" <?php echo $selectedProductId === (int) $offerOption['id'] ? 'selected' : ''; ?>>
                                            <?php echo htmlspecialchars($optionType . ' - ' . (string) $offerOption['nombre'], ENT_QUOTES, 'UTF-8'); ?>
                                        </option>
                                    <?php endforeach; ?>
                                </select>
                                <small class="form-text text-muted">Si lo seleccionas, se toma como base para el nombre del certificado.</small>
                            </div>
                        <?php endif; ?>

                        <div class="form-group">
                            <label><strong>Nombre institucional</strong></label>
                            <input type="text" name="institution_name" class="form-control" maxlength="120" value="<?php echo htmlspecialchars((string) $certificateData['institution_name'], ENT_QUOTES, 'UTF-8'); ?>">
                        </div>

                        <div class="form-group">
                            <label><strong>Nombre del estudiante</strong></label>
                            <input type="text" name="student_name" class="form-control" maxlength="140" value="<?php echo htmlspecialchars((string) $certificateData['student_name'], ENT_QUOTES, 'UTF-8'); ?>" required>
                        </div>

                        <div class="form-group">
                            <label><strong>Nombre del certificado</strong></label>
                            <input type="text" name="certificate_name" class="form-control" maxlength="140" value="<?php echo htmlspecialchars((string) $certificateData['certificate_name'], ENT_QUOTES, 'UTF-8'); ?>" required>
                        </div>

                        <div class="form-group">
                            <label><strong>Subtítulo</strong></label>
                            <input type="text" name="certificate_subtitle" class="form-control" maxlength="160" value="<?php echo htmlspecialchars((string) $certificateData['certificate_subtitle'], ENT_QUOTES, 'UTF-8'); ?>">
                        </div>

                        <div class="form-row">
                            <div class="form-group col-md-5">
                                <label><strong>Lugar</strong></label>
                                <input type="text" name="location" class="form-control" maxlength="80" value="<?php echo htmlspecialchars((string) $certificateData['location'], ENT_QUOTES, 'UTF-8'); ?>">
                            </div>
                            <div class="form-group col-md-2">
                                <label><strong>Día</strong></label>
                                <input type="number" name="day" class="form-control" min="1" max="31" value="<?php echo htmlspecialchars((string) $certificateData['day'], ENT_QUOTES, 'UTF-8'); ?>">
                            </div>
                            <div class="form-group col-md-3">
                                <label><strong>Mes</strong></label>
                                <select name="month" class="form-control">
                                    <?php foreach (atenea_certificate_months() as $monthValue => $monthLabel) : ?>
                                        <option value="<?php echo htmlspecialchars($monthValue, ENT_QUOTES, 'UTF-8'); ?>" <?php echo $certificateData['month'] === $monthValue ? 'selected' : ''; ?>>
                                            <?php echo htmlspecialchars(ucfirst($monthLabel), ENT_QUOTES, 'UTF-8'); ?>
                                        </option>
                                    <?php endforeach; ?>
                                </select>
                            </div>
                            <div class="form-group col-md-2">
                                <label><strong>Año</strong></label>
                                <input type="text" name="year" class="form-control" maxlength="4" value="<?php echo htmlspecialchars((string) $certificateData['year'], ENT_QUOTES, 'UTF-8'); ?>">
                            </div>
                        </div>

                        <div class="form-group">
                            <label><strong>Texto de respaldo</strong></label>
                            <textarea name="cooperative_text" class="form-control" rows="4" maxlength="320"><?php echo htmlspecialchars((string) $certificateData['cooperative_text'], ENT_QUOTES, 'UTF-8'); ?></textarea>
                        </div>

                        <div class="form-row">
                            <div class="form-group col-md-6">
                                <label><strong>Firma principal</strong></label>
                                <input type="text" name="president_name" class="form-control" maxlength="120" value="<?php echo htmlspecialchars((string) $certificateData['president_name'], ENT_QUOTES, 'UTF-8'); ?>">
                            </div>
                            <div class="form-group col-md-6">
                                <label><strong>Cargo principal</strong></label>
                                <input type="text" name="president_role" class="form-control" maxlength="80" value="<?php echo htmlspecialchars((string) $certificateData['president_role'], ENT_QUOTES, 'UTF-8'); ?>">
                            </div>
                        </div>

                        <div class="form-row">
                            <div class="form-group col-md-6">
                                <label><strong>Nombre de facilitador</strong> (opcional)</label>
                                <input type="text" name="facilitator_name" class="form-control" maxlength="120" value="<?php echo htmlspecialchars((string) $certificateData['facilitator_name'], ENT_QUOTES, 'UTF-8'); ?>">
                            </div>
                            <div class="form-group col-md-6">
                                <label><strong>Cargo facilitador</strong></label>
                                <input type="text" name="facilitator_role" class="form-control" maxlength="80" value="<?php echo htmlspecialchars((string) $certificateData['facilitator_role'], ENT_QUOTES, 'UTF-8'); ?>">
                            </div>
                        </div>

                        <div class="d-flex flex-wrap gap-2">
                            <button type="submit" class="btn btn-success">
                                <i class="fas fa-sync-alt"></i> Actualizar vista previa
                            </button>
                            <a href="<?php echo htmlspecialchars($pdfUrl, ENT_QUOTES, 'UTF-8'); ?>" target="_blank" class="btn btn-primary">
                                <i class="fas fa-file-download"></i> Abrir PDF
                            </a>
                        </div>
                    </form>
                </div>
            </div>

            <div>
                <div class="atenea-certificate-note mb-3">
                    Plantilla basada directamente en la imagen de referencia: fondo rosado/lila claro, franja ornamental verde/gris, doble marco punteado, logo lateral y cinta superior.
                </div>
                <div class="atenea-certificate-preview-wrap">
                    <?php echo atenea_certificate_html($certificateData, '../img/certificados/certificado_base.jpg'); ?>
                </div>
            </div>
        </div>
    </div>
</div>

<?php include '../includes/footer.php'; ?>
