<?php

if (!function_exists('atenea_certificate_bootstrap')) {
    function atenea_certificate_bootstrap(): void
    {
        static $booted = false;

        if ($booted) {
            return;
        }

        $autoload = dirname(__DIR__) . DIRECTORY_SEPARATOR . 'vendor' . DIRECTORY_SEPARATOR . 'autoload.php';
        if (is_file($autoload)) {
            require_once $autoload;
        }

        if (!class_exists('TCPDF')) {
            $tcpdfPath = dirname(__DIR__) . DIRECTORY_SEPARATOR . 'vendor' . DIRECTORY_SEPARATOR . 'tecnickcom' . DIRECTORY_SEPARATOR . 'tcpdf' . DIRECTORY_SEPARATOR . 'tcpdf.php';
            if (is_file($tcpdfPath)) {
                require_once $tcpdfPath;
            }
        }

        if (!class_exists('TCPDF')) {
            throw new RuntimeException('TCPDF no esta disponible para generar certificados.');
        }

        $booted = true;
    }
}

if (!function_exists('atenea_certificate_months')) {
    function atenea_certificate_months(): array
    {
        return [
            'enero' => 'enero',
            'febrero' => 'febrero',
            'marzo' => 'marzo',
            'abril' => 'abril',
            'mayo' => 'mayo',
            'junio' => 'junio',
            'julio' => 'julio',
            'agosto' => 'agosto',
            'septiembre' => 'septiembre',
            'octubre' => 'octubre',
            'noviembre' => 'noviembre',
            'diciembre' => 'diciembre',
        ];
    }
}

if (!function_exists('atenea_certificate_clean_text')) {
    function atenea_certificate_clean_text(?string $value, int $maxLength = 255): string
    {
        $value = trim((string) $value);
        $value = preg_replace('/\s+/', ' ', $value) ?? '';

        return function_exists('mb_substr')
            ? mb_substr($value, 0, $maxLength, 'UTF-8')
            : substr($value, 0, $maxLength);
    }
}

if (!function_exists('atenea_certificate_default_data')) {
    function atenea_certificate_default_data(): array
    {
        $monthNames = array_values(atenea_certificate_months());

        return [
            'student_name' => 'Nombre del estudiante',
            'certificate_name' => 'LIMPIEZA DE OIDOS',
            'certificate_subtitle' => 'Ear Candling - Conoterapia Nivel I',
            'location' => 'San Salvador',
            'day' => (string) date('j'),
            'month' => $monthNames[(int) date('n') - 1] ?? 'julio',
            'year' => (string) date('Y'),
            'institution_name' => 'Escuela de Naturopatía Holística',
            'president_name' => 'DLIM Roberto Quinteros',
            'president_role' => 'PRESIDENTE',
            'facilitator_name' => '',
            'facilitator_role' => 'Facilitador',
            'cooperative_text' => 'Con el respaldo institucional de nuestra cooperativa formativa, comprometida con la capacitación ética, consciente y profesional en terapias naturales y bienestar integral.',
        ];
    }
}

if (!function_exists('atenea_certificate_build_data')) {
    function atenea_certificate_build_data(array $input = []): array
    {
        $defaults = atenea_certificate_default_data();
        $months = atenea_certificate_months();

        $data = $defaults;
        $data['student_name'] = atenea_certificate_clean_text((string) ($input['student_name'] ?? $defaults['student_name']), 140);
        $data['certificate_name'] = atenea_certificate_clean_text((string) ($input['certificate_name'] ?? $defaults['certificate_name']), 140);
        $data['certificate_subtitle'] = atenea_certificate_clean_text((string) ($input['certificate_subtitle'] ?? $defaults['certificate_subtitle']), 160);
        $data['location'] = atenea_certificate_clean_text((string) ($input['location'] ?? $defaults['location']), 80);
        $data['institution_name'] = atenea_certificate_clean_text((string) ($input['institution_name'] ?? $defaults['institution_name']), 120);
        $data['president_name'] = atenea_certificate_clean_text((string) ($input['president_name'] ?? $defaults['president_name']), 120);
        $data['president_role'] = atenea_certificate_clean_text((string) ($input['president_role'] ?? $defaults['president_role']), 80);
        $data['facilitator_name'] = atenea_certificate_clean_text((string) ($input['facilitator_name'] ?? $defaults['facilitator_name']), 120);
        $data['facilitator_role'] = atenea_certificate_clean_text((string) ($input['facilitator_role'] ?? $defaults['facilitator_role']), 80);
        $data['cooperative_text'] = atenea_certificate_clean_text((string) ($input['cooperative_text'] ?? $defaults['cooperative_text']), 320);

        $day = (int) ($input['day'] ?? $defaults['day']);
        if ($day < 1 || $day > 31) {
            $day = (int) $defaults['day'];
        }
        $data['day'] = (string) $day;

        $year = preg_replace('/\D+/', '', (string) ($input['year'] ?? $defaults['year'])) ?? '';
        if ($year === '' || strlen($year) < 4) {
            $year = (string) $defaults['year'];
        }
        $data['year'] = substr($year, 0, 4);

        $month = strtolower(atenea_certificate_clean_text((string) ($input['month'] ?? $defaults['month']), 20));
        $data['month'] = array_key_exists($month, $months) ? $months[$month] : $defaults['month'];

        if ($data['student_name'] === '') {
            $data['student_name'] = $defaults['student_name'];
        }

        if ($data['certificate_name'] === '') {
            $data['certificate_name'] = $defaults['certificate_name'];
        }

        if ($data['certificate_subtitle'] === '') {
            $data['certificate_subtitle'] = $defaults['certificate_subtitle'];
        }

        if ($data['location'] === '') {
            $data['location'] = $defaults['location'];
        }

        return $data;
    }
}

if (!function_exists('atenea_certificate_query_data')) {
    function atenea_certificate_query_data(array $data): array
    {
        return [
            'student_name' => (string) ($data['student_name'] ?? ''),
            'certificate_name' => (string) ($data['certificate_name'] ?? ''),
            'certificate_subtitle' => (string) ($data['certificate_subtitle'] ?? ''),
            'location' => (string) ($data['location'] ?? ''),
            'day' => (string) ($data['day'] ?? ''),
            'month' => (string) ($data['month'] ?? ''),
            'year' => (string) ($data['year'] ?? ''),
            'institution_name' => (string) ($data['institution_name'] ?? ''),
            'president_name' => (string) ($data['president_name'] ?? ''),
            'president_role' => (string) ($data['president_role'] ?? ''),
            'facilitator_name' => (string) ($data['facilitator_name'] ?? ''),
            'facilitator_role' => (string) ($data['facilitator_role'] ?? ''),
            'cooperative_text' => (string) ($data['cooperative_text'] ?? ''),
        ];
    }
}

if (!function_exists('atenea_certificate_upper')) {
    function atenea_certificate_upper(string $value): string
    {
        return function_exists('mb_strtoupper')
            ? mb_strtoupper($value, 'UTF-8')
            : strtoupper($value);
    }
}

if (!function_exists('atenea_certificate_preview_css')) {
    function atenea_certificate_preview_css(): string
    {
        return <<<'CSS'
.atenea-certificate-preview-wrap {
  background: linear-gradient(180deg, #ffffff 0%, #f8faf8 100%);
  border-radius: 1.5rem;
  padding: 1.5rem;
}

.atenea-certificate {
  position: relative;
  max-width: 1200px;
  margin: 0 auto;
  background: #f7eef4;
  border: 3px solid #93a49b;
  box-shadow: 0 20px 45px rgba(76, 94, 83, 0.14);
  padding: 20px;
  color: #405449;
}

.atenea-certificate::before {
  content: "";
  position: absolute;
  inset: 8px;
  border: 1px solid #c4d0c8;
  pointer-events: none;
}

.atenea-certificate::after {
  content: "";
  position: absolute;
  inset: 18px;
  border: 2px dashed #9fb1a6;
  pointer-events: none;
}

.atenea-certificate__ornament {
  position: absolute;
  color: #7f9187;
  font-family: Georgia, "Times New Roman", serif;
  font-size: 2.8rem;
  line-height: 1;
  z-index: 1;
}

.atenea-certificate__ornament--tl { top: 12px; left: 18px; }
.atenea-certificate__ornament--tr { top: 12px; right: 18px; transform: scaleX(-1); }
.atenea-certificate__ornament--bl { bottom: 12px; left: 18px; transform: scaleY(-1); }
.atenea-certificate__ornament--br { bottom: 12px; right: 18px; transform: scale(-1); }

.atenea-certificate__inner {
  position: relative;
  z-index: 2;
  min-height: 760px;
  display: flex;
  flex-direction: column;
  justify-content: space-between;
  text-align: center;
  padding: 28px 44px 24px;
}

.atenea-certificate__brand {
  color: #4f665a;
  font-family: Georgia, "Times New Roman", serif;
  font-size: 2.6rem;
  font-weight: 700;
  letter-spacing: 0.2rem;
  margin-bottom: 0.35rem;
}

.atenea-certificate__school {
  color: #6a7f73;
  font-size: 1.15rem;
  letter-spacing: 0.08rem;
  text-transform: uppercase;
}

.atenea-certificate__rule {
  margin: 1rem auto 0.35rem;
  color: #8ba095;
  font-size: 1.15rem;
  letter-spacing: 0.45rem;
}

.atenea-certificate__heading {
  color: #556b5f;
  font-family: Georgia, "Times New Roman", serif;
  font-size: 1.5rem;
  font-weight: 700;
  letter-spacing: 0.18rem;
  margin-top: 0.8rem;
}

.atenea-certificate__student {
  color: #355443;
  font-family: "Brush Script MT", "Lucida Handwriting", cursive;
  font-size: 3.4rem;
  line-height: 1.15;
  margin: 1rem 0 0.6rem;
}

.atenea-certificate__body {
  max-width: 860px;
  margin: 0 auto;
  color: #54685d;
  font-size: 1.18rem;
  line-height: 1.55;
}

.atenea-certificate__name {
  color: #4d665a;
  font-family: Georgia, "Times New Roman", serif;
  font-size: 2.55rem;
  font-weight: 700;
  letter-spacing: 0.08rem;
  margin: 1rem 0 0.3rem;
  text-transform: uppercase;
}

.atenea-certificate__subtitle {
  color: #697d73;
  font-size: 1.3rem;
  font-style: italic;
  margin-bottom: 1rem;
}

.atenea-certificate__support {
  max-width: 920px;
  margin: 0.3rem auto 1rem;
  color: #66796f;
  font-size: 1rem;
  line-height: 1.5;
}

.atenea-certificate__date {
  color: #54685d;
  font-size: 1.08rem;
  margin-top: 1.2rem;
}

.atenea-certificate__signatures {
  display: flex;
  justify-content: space-between;
  gap: 2rem;
  margin-top: 2rem;
}

.atenea-certificate__signature {
  flex: 1;
  text-align: center;
}

.atenea-certificate__line {
  border-top: 1.8px solid #7f9187;
  margin: 0 auto 0.6rem;
  max-width: 280px;
}

.atenea-certificate__signature-name {
  color: #3f5548;
  font-size: 1.08rem;
  font-weight: 700;
  min-height: 1.5rem;
}

.atenea-certificate__signature-role {
  color: #6a7d73;
  font-size: 0.95rem;
  letter-spacing: 0.1rem;
  text-transform: uppercase;
}

@media (max-width: 991.98px) {
  .atenea-certificate__inner {
    min-height: auto;
    padding: 24px 26px;
  }

  .atenea-certificate__student {
    font-size: 2.6rem;
  }

  .atenea-certificate__name {
    font-size: 2rem;
  }
}

@media (max-width: 767.98px) {
  .atenea-certificate {
    padding: 14px;
  }

  .atenea-certificate__inner {
    padding: 20px 18px;
  }

  .atenea-certificate__brand {
    font-size: 2rem;
    letter-spacing: 0.1rem;
  }

  .atenea-certificate__school,
  .atenea-certificate__body,
  .atenea-certificate__support,
  .atenea-certificate__date {
    font-size: 0.98rem;
  }

  .atenea-certificate__heading {
    font-size: 1.15rem;
  }

  .atenea-certificate__student {
    font-size: 2rem;
  }

  .atenea-certificate__name {
    font-size: 1.55rem;
  }

  .atenea-certificate__subtitle {
    font-size: 1rem;
  }

  .atenea-certificate__signatures {
    flex-direction: column;
  }
}
CSS;
    }
}

if (!function_exists('atenea_certificate_html')) {
    function atenea_certificate_html(array $data): string
    {
        $data = atenea_certificate_build_data($data);
        $studentName = htmlspecialchars((string) $data['student_name'], ENT_QUOTES, 'UTF-8');
        $certificateName = htmlspecialchars(atenea_certificate_upper((string) $data['certificate_name']), ENT_QUOTES, 'UTF-8');
        $subtitle = htmlspecialchars((string) $data['certificate_subtitle'], ENT_QUOTES, 'UTF-8');
        $location = htmlspecialchars((string) $data['location'], ENT_QUOTES, 'UTF-8');
        $day = htmlspecialchars((string) $data['day'], ENT_QUOTES, 'UTF-8');
        $month = htmlspecialchars((string) $data['month'], ENT_QUOTES, 'UTF-8');
        $year = htmlspecialchars((string) $data['year'], ENT_QUOTES, 'UTF-8');
        $institution = htmlspecialchars((string) $data['institution_name'], ENT_QUOTES, 'UTF-8');
        $presidentName = htmlspecialchars((string) $data['president_name'], ENT_QUOTES, 'UTF-8');
        $presidentRole = htmlspecialchars((string) $data['president_role'], ENT_QUOTES, 'UTF-8');
        $facilitatorName = htmlspecialchars((string) $data['facilitator_name'], ENT_QUOTES, 'UTF-8');
        $facilitatorRole = htmlspecialchars((string) $data['facilitator_role'], ENT_QUOTES, 'UTF-8');
        $supportText = htmlspecialchars((string) $data['cooperative_text'], ENT_QUOTES, 'UTF-8');

        ob_start();
        ?>
        <div class="atenea-certificate">
          <div class="atenea-certificate__ornament atenea-certificate__ornament--tl">❦</div>
          <div class="atenea-certificate__ornament atenea-certificate__ornament--tr">❦</div>
          <div class="atenea-certificate__ornament atenea-certificate__ornament--bl">❦</div>
          <div class="atenea-certificate__ornament atenea-certificate__ornament--br">❦</div>
          <div class="atenea-certificate__inner">
            <div>
              <div class="atenea-certificate__brand">ATENEA</div>
              <div class="atenea-certificate__school"><?php echo $institution; ?></div>
              <div class="atenea-certificate__rule">❦ ❦ ❦</div>
              <div class="atenea-certificate__heading">CERTIFICA A:</div>
              <div class="atenea-certificate__student"><?php echo $studentName; ?></div>
              <p class="atenea-certificate__body">
                Estudiante que cumplió los requisitos exigidos para el CERTIFICADO de
              </p>
              <div class="atenea-certificate__name"><?php echo $certificateName; ?></div>
              <div class="atenea-certificate__subtitle"><?php echo $subtitle; ?></div>
              <p class="atenea-certificate__support"><?php echo $supportText; ?></p>
            </div>
            <div>
              <p class="atenea-certificate__date">
                <?php echo $location; ?> a los <?php echo $day; ?> días del mes de <?php echo $month; ?> de <?php echo $year; ?>
              </p>
              <div class="atenea-certificate__signatures">
                <div class="atenea-certificate__signature">
                  <div class="atenea-certificate__line"></div>
                  <div class="atenea-certificate__signature-name"><?php echo $presidentName; ?></div>
                  <div class="atenea-certificate__signature-role"><?php echo $presidentRole; ?></div>
                </div>
                <div class="atenea-certificate__signature">
                  <div class="atenea-certificate__line"></div>
                  <div class="atenea-certificate__signature-name"><?php echo $facilitatorName !== '' ? $facilitatorName : '&nbsp;'; ?></div>
                  <div class="atenea-certificate__signature-role"><?php echo $facilitatorRole; ?></div>
                </div>
              </div>
            </div>
          </div>
        </div>
        <?php

        return (string) ob_get_clean();
    }
}

if (!function_exists('atenea_certificate_pdf_binary')) {
    function atenea_certificate_pdf_binary(array $data): string
    {
        atenea_certificate_bootstrap();

        $data = atenea_certificate_build_data($data);
        $certificateName = atenea_certificate_upper((string) $data['certificate_name']);

        $pdf = new TCPDF('L', 'mm', 'A4', true, 'UTF-8', false);
        $pdf->SetCreator('Atenea');
        $pdf->SetAuthor('Atenea');
        $pdf->SetTitle('Certificado - ' . $certificateName);
        $pdf->SetPrintHeader(false);
        $pdf->SetPrintFooter(false);
        $pdf->SetMargins(0, 0, 0);
        $pdf->SetAutoPageBreak(false, 0);
        $pdf->AddPage();

        $pageWidth = 297.0;
        $pageHeight = 210.0;

        $pdf->SetFillColor(247, 238, 244);
        $pdf->Rect(0, 0, $pageWidth, $pageHeight, 'F');

        $pdf->SetDrawColor(146, 164, 153);
        $pdf->SetLineWidth(0.9);
        $pdf->Rect(8.5, 8.5, 280.0, 193.0, 'D');

        $pdf->SetDrawColor(192, 205, 197);
        $pdf->SetLineWidth(0.35);
        $pdf->Rect(12.5, 12.5, 272.0, 185.0, 'D');

        $pdf->SetLineStyle(['width' => 0.3, 'color' => [162, 177, 168], 'dash' => '2,2']);
        $pdf->Rect(18.5, 18.5, 260.0, 173.0, 'D');
        $pdf->SetLineStyle(['width' => 0.2, 'color' => [0, 0, 0], 'dash' => 0]);

        $pdf->SetTextColor(123, 140, 131);
        $pdf->SetFont('dejavuserif', '', 22);
        $pdf->Text(15.5, 15.5, '❦');
        $pdf->Text(274.0, 15.5, '❦');
        $pdf->Text(15.5, 181.0, '❦');
        $pdf->Text(274.0, 181.0, '❦');

        $pdf->SetFont('dejavuserif', 'B', 24);
        $pdf->SetTextColor(79, 102, 90);
        $pdf->SetXY(35, 26);
        $pdf->Cell(227, 10, 'ATENEA', 0, 1, 'C');

        $pdf->SetFont('dejavuserif', '', 12);
        $pdf->SetTextColor(102, 124, 111);
        $pdf->SetX(35);
        $pdf->Cell(227, 7, (string) $data['institution_name'], 0, 1, 'C');

        $pdf->SetFont('dejavuserif', '', 11);
        $pdf->SetTextColor(139, 160, 149);
        $pdf->SetX(35);
        $pdf->Cell(227, 7, '❦   ❦   ❦', 0, 1, 'C');

        $pdf->SetFont('dejavuserif', 'B', 14);
        $pdf->SetTextColor(86, 107, 95);
        $pdf->SetX(35);
        $pdf->Cell(227, 9, 'CERTIFICA A:', 0, 1, 'C');

        $pdf->SetFont('dejavuserif', 'I', 28);
        $pdf->SetTextColor(53, 84, 67);
        $pdf->SetXY(30, 66);
        $pdf->MultiCell(237, 16, (string) $data['student_name'], 0, 'C');

        $pdf->SetFont('dejavuserif', '', 12);
        $pdf->SetTextColor(84, 104, 93);
        $pdf->SetXY(42, 92);
        $pdf->MultiCell(213, 8, 'Estudiante que cumplió los requisitos exigidos para el CERTIFICADO de', 0, 'C');

        $pdf->SetFont('dejavuserif', 'B', 22);
        $pdf->SetTextColor(77, 102, 90);
        $pdf->SetXY(32, 108);
        $pdf->MultiCell(233, 12, $certificateName, 0, 'C');

        $pdf->SetFont('dejavuserif', 'I', 13);
        $pdf->SetTextColor(103, 125, 115);
        $pdf->SetXY(44, 124);
        $pdf->MultiCell(209, 8, (string) $data['certificate_subtitle'], 0, 'C');

        $pdf->SetFont('dejavuserif', '', 10);
        $pdf->SetTextColor(102, 121, 111);
        $pdf->SetXY(34, 138);
        $pdf->MultiCell(229, 10, (string) $data['cooperative_text'], 0, 'C');

        $pdf->SetFont('dejavuserif', '', 11);
        $pdf->SetTextColor(84, 104, 93);
        $pdf->SetXY(30, 159);
        $pdf->Cell(
            237,
            8,
            (string) $data['location'] . ' a los ' . (string) $data['day'] . ' días del mes de ' . (string) $data['month'] . ' de ' . (string) $data['year'],
            0,
            1,
            'C'
        );

        $lineY = 176.5;
        $leftX = 52.0;
        $rightX = 171.0;
        $lineWidth = 74.0;

        $pdf->SetDrawColor(127, 145, 135);
        $pdf->SetLineWidth(0.45);
        $pdf->Line($leftX, $lineY, $leftX + $lineWidth, $lineY);
        $pdf->Line($rightX, $lineY, $rightX + $lineWidth, $lineY);

        $pdf->SetFont('dejavuserif', 'B', 10.5);
        $pdf->SetTextColor(63, 85, 72);
        $pdf->SetXY($leftX - 8, $lineY + 2.5);
        $pdf->Cell($lineWidth + 16, 6, (string) $data['president_name'], 0, 1, 'C');

        $pdf->SetFont('dejavuserif', '', 9.5);
        $pdf->SetTextColor(106, 125, 115);
        $pdf->SetXY($leftX - 8, $lineY + 9);
        $pdf->Cell($lineWidth + 16, 5, (string) $data['president_role'], 0, 1, 'C');

        $pdf->SetFont('dejavuserif', 'B', 10.5);
        $pdf->SetTextColor(63, 85, 72);
        $pdf->SetXY($rightX - 8, $lineY + 2.5);
        $pdf->Cell($lineWidth + 16, 6, (string) ($data['facilitator_name'] !== '' ? $data['facilitator_name'] : ' '), 0, 1, 'C');

        $pdf->SetFont('dejavuserif', '', 9.5);
        $pdf->SetTextColor(106, 125, 115);
        $pdf->SetXY($rightX - 8, $lineY + 9);
        $pdf->Cell($lineWidth + 16, 5, (string) $data['facilitator_role'], 0, 1, 'C');

        return (string) $pdf->Output('', 'S');
    }
}
