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

if (!function_exists('atenea_certificate_length')) {
    function atenea_certificate_length(string $value): int
    {
        return function_exists('mb_strlen')
            ? mb_strlen($value, 'UTF-8')
            : strlen($value);
    }
}

if (!function_exists('atenea_certificate_default_data')) {
    function atenea_certificate_default_data(): array
    {
        $monthNames = array_values(atenea_certificate_months());

        return [
            'student_name' => 'Reynalda Dávila B.',
            'certificate_name' => 'LIMPIEZA DE OIDOS',
            'certificate_subtitle' => 'Ear Candling - Conoterapia Nivel I',
            'location' => 'San Salvador',
            'day' => (string) date('j'),
            'month' => $monthNames[(int) date('n') - 1] ?? 'julio',
            'year' => (string) date('Y'),
            'institution_name' => 'Escuela de Naturopatía Holística',
            'president_name' => 'DLIM Roberto Quinteros',
            'president_role' => 'PRESIDENTE',
            'facilitator_name' => 'DLIM Roberto Quinteros',
            'facilitator_role' => 'Facilitador',
            'cooperative_text' => 'Con el Respaldo de la Cooperativa PRECURSORES RL',
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
        $data['cooperative_text'] = atenea_certificate_clean_text((string) ($input['cooperative_text'] ?? $defaults['cooperative_text']), 220);

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

        foreach (['student_name', 'certificate_name', 'certificate_subtitle', 'location', 'institution_name', 'president_name', 'president_role', 'facilitator_role', 'cooperative_text'] as $field) {
            if ($data[$field] === '') {
                $data[$field] = $defaults[$field];
            }
        }

        if ($data['facilitator_name'] === '') {
            $data['facilitator_name'] = $defaults['facilitator_name'];
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

if (!function_exists('atenea_certificate_background_relative_path')) {
    function atenea_certificate_background_relative_path(): string
    {
        return '../img/certificados/certificado_base.jpg';
    }
}

if (!function_exists('atenea_certificate_background_absolute_path')) {
    function atenea_certificate_background_absolute_path(): ?string
    {
        $path = __DIR__ . '/../img/certificados/certificado_base.jpg';

        return is_file($path) ? $path : null;
    }
}

if (!function_exists('atenea_certificate_ratio')) {
    function atenea_certificate_ratio(float $value, float $total): string
    {
        return number_format(($value / $total) * 100, 6, '.', '') . '%';
    }
}

if (!function_exists('atenea_certificate_html_student_size')) {
    function atenea_certificate_html_student_size(string $studentName): string
    {
        $length = atenea_certificate_length($studentName);

        if ($length <= 18) {
            return '5.6rem';
        }

        if ($length <= 26) {
            return '5rem';
        }

        if ($length <= 34) {
            return '4.35rem';
        }

        return '3.7rem';
    }
}

if (!function_exists('atenea_certificate_html_title_size')) {
    function atenea_certificate_html_title_size(string $title): string
    {
        $length = atenea_certificate_length($title);

        if ($length <= 20) {
            return '3.55rem';
        }

        if ($length <= 28) {
            return '3.15rem';
        }

        if ($length <= 36) {
            return '2.75rem';
        }

        return '2.35rem';
    }
}

if (!function_exists('atenea_certificate_pdf_student_size')) {
    function atenea_certificate_pdf_student_size(string $studentName): float
    {
        $length = atenea_certificate_length($studentName);

        if ($length <= 18) {
            return 33.0;
        }

        if ($length <= 26) {
            return 29.0;
        }

        if ($length <= 34) {
            return 25.0;
        }

        return 21.5;
    }
}

if (!function_exists('atenea_certificate_pdf_title_size')) {
    function atenea_certificate_pdf_title_size(string $title): float
    {
        $length = atenea_certificate_length($title);

        if ($length <= 20) {
            return 22.0;
        }

        if ($length <= 28) {
            return 19.2;
        }

        if ($length <= 36) {
            return 16.8;
        }

        return 14.5;
    }
}

if (!function_exists('atenea_certificate_windows_font')) {
    function atenea_certificate_windows_font(array $candidates): ?string
    {
        $windowsDir = getenv('WINDIR') ?: 'C:\\Windows';

        foreach ($candidates as $candidate) {
            $path = $windowsDir . DIRECTORY_SEPARATOR . 'Fonts' . DIRECTORY_SEPARATOR . $candidate;
            if (is_file($path)) {
                return $path;
            }
        }

        return null;
    }
}

if (!function_exists('atenea_certificate_register_tcpdf_font')) {
    function atenea_certificate_register_tcpdf_font(array $candidates, string $fallback): string
    {
        static $cache = [];

        atenea_certificate_bootstrap();

        $fontPath = atenea_certificate_windows_font($candidates);
        if ($fontPath === null) {
            return $fallback;
        }

        if (isset($cache[$fontPath])) {
            return $cache[$fontPath];
        }

        $registered = TCPDF_FONTS::addTTFfont($fontPath, 'TrueTypeUnicode', '', 32);
        if ($registered === false) {
            return $fallback;
        }

        $cache[$fontPath] = $registered;

        return $registered;
    }
}

if (!function_exists('atenea_certificate_pdf_fonts')) {
    function atenea_certificate_pdf_fonts(): array
    {
        static $fonts = null;

        if (is_array($fonts)) {
            return $fonts;
        }

        $fonts = [
            'school' => atenea_certificate_register_tcpdf_font(['OLDENGL.TTF'], 'times'),
            'student' => atenea_certificate_register_tcpdf_font(['MTCORSVA.TTF', 'FRSCRIPT.TTF', 'SCRIPTBL.TTF'], 'times'),
            'body' => atenea_certificate_register_tcpdf_font(['GARA.TTF', 'pala.ttf'], 'dejavuserif'),
            'body_bold' => atenea_certificate_register_tcpdf_font(['GARABD.TTF', 'palab.ttf'], 'dejavuserif'),
            'title' => atenea_certificate_register_tcpdf_font(['GARABD.TTF', 'BOOKOSB.TTF', 'palab.ttf'], 'dejavuserif'),
        ];

        return $fonts;
    }
}

if (!function_exists('atenea_certificate_preview_css')) {
    function atenea_certificate_preview_css(): string
    {
        return <<<'CSS'
.atenea-certificate-preview-wrap {
  background: linear-gradient(180deg, #edf4ef 0%, #f7faf8 100%);
  border-radius: 1.2rem;
  padding: 1.25rem;
  overflow: auto;
}

.atenea-certificate {
  position: relative;
  width: min(100%, 1152px);
  margin: 0 auto;
  aspect-ratio: 4 / 3;
  background-color: #f7eef4;
  background-position: center;
  background-repeat: no-repeat;
  background-size: 100% 100%;
  box-shadow: 0 22px 55px rgba(44, 58, 49, 0.18);
}

.atenea-certificate__item {
  position: absolute;
  text-align: center;
  z-index: 2;
}

.atenea-certificate__school {
  left: 21.701389%;
  top: 11.111111%;
  width: 68.576389%;
  color: #cf8a8d;
  font-family: "Old English Text MT", "Goudy Text MT", "Book Antiqua", serif;
  font-size: 3.55rem;
  line-height: 1;
  letter-spacing: 0.01em;
  text-shadow: 0 1px 0 rgba(255, 255, 255, 0.65);
}

.atenea-certificate__label {
  left: 44.010417%;
  top: 21.180556%;
  width: 18.923611%;
  color: #c17f77;
  font-family: Garamond, "Palatino Linotype", "Book Antiqua", serif;
  font-size: 1.85rem;
  font-weight: 700;
  letter-spacing: 0.03em;
}

.atenea-certificate__student {
  left: 25.694444%;
  top: 25.347222%;
  width: 58.333333%;
  color: #4e4d70;
  font-family: "Monotype Corsiva", "French Script MT", "Brush Script MT", cursive;
  line-height: 1;
  white-space: nowrap;
}

.atenea-certificate__body {
  left: 9.548611%;
  top: 41.087963%;
  width: 80.295139%;
  color: #322c35;
  font-family: Garamond, "Palatino Linotype", "Book Antiqua", serif;
  font-size: 1.85rem;
  font-weight: 600;
  line-height: 1.15;
}

.atenea-certificate__body strong {
  font-weight: 700;
}

.atenea-certificate__title {
  left: 21.267361%;
  top: 47.222222%;
  width: 57.638889%;
  color: #d78195;
  font-family: Garamond, "Palatino Linotype", "Book Antiqua", serif;
  font-weight: 700;
  letter-spacing: 0.18em;
  line-height: 1;
  text-transform: uppercase;
  white-space: nowrap;
}

.atenea-certificate__subtitle {
  left: 22.135417%;
  top: 55.902778%;
  width: 55.989583%;
  color: #3a3542;
  font-family: Garamond, "Palatino Linotype", "Book Antiqua", serif;
  font-size: 2.35rem;
  line-height: 1.05;
  letter-spacing: 0.02em;
}

.atenea-certificate__signature {
  color: #2e2834;
  font-family: Garamond, "Palatino Linotype", "Book Antiqua", serif;
  font-size: 1.55rem;
  line-height: 1.05;
}

.atenea-certificate__signature strong {
  display: block;
  font-size: 1.65rem;
  font-weight: 700;
}

.atenea-certificate__signature--left {
  left: 21.180556%;
  top: 67.245370%;
  width: 24.305556%;
}

.atenea-certificate__signature--right {
  left: 50.260417%;
  top: 67.245370%;
  width: 24.826389%;
}

.atenea-certificate__support {
  left: 11.631944%;
  top: 79.513889%;
  width: 47.743056%;
  color: #403744;
  font-family: Garamond, "Palatino Linotype", "Book Antiqua", serif;
  font-size: 1.28rem;
  line-height: 1.05;
  white-space: nowrap;
}

.atenea-certificate__date {
  left: 24.131944%;
  top: 84.259259%;
  width: 53.819444%;
  color: #403744;
  font-family: Garamond, "Palatino Linotype", "Book Antiqua", serif;
  font-size: 1.6rem;
  font-weight: 700;
  line-height: 1.05;
  white-space: nowrap;
}

@media (max-width: 1199.98px) {
  .atenea-certificate__school {
    font-size: 3rem;
  }

  .atenea-certificate__label {
    font-size: 1.45rem;
  }

  .atenea-certificate__body {
    font-size: 1.45rem;
  }

  .atenea-certificate__subtitle {
    font-size: 1.9rem;
  }

  .atenea-certificate__signature {
    font-size: 1.18rem;
  }

  .atenea-certificate__signature strong {
    font-size: 1.28rem;
  }

  .atenea-certificate__support {
    font-size: 1rem;
  }

  .atenea-certificate__date {
    font-size: 1.22rem;
  }
}

@media (max-width: 767.98px) {
  .atenea-certificate-preview-wrap {
    padding: 0.75rem;
  }

  .atenea-certificate__school {
    font-size: 1.75rem;
  }

  .atenea-certificate__label {
    font-size: 0.95rem;
  }

  .atenea-certificate__body {
    font-size: 0.96rem;
  }

  .atenea-certificate__subtitle {
    font-size: 1.15rem;
  }

  .atenea-certificate__signature {
    font-size: 0.78rem;
  }

  .atenea-certificate__signature strong {
    font-size: 0.85rem;
  }

  .atenea-certificate__support {
    font-size: 0.66rem;
  }

  .atenea-certificate__date {
    font-size: 0.8rem;
  }
}
CSS;
    }
}

if (!function_exists('atenea_certificate_html')) {
    function atenea_certificate_html(array $data, string $backgroundUrl = '../img/certificados/certificado_base.jpg'): string
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
        $backgroundStyle = "background-image:url('" . htmlspecialchars($backgroundUrl, ENT_QUOTES, 'UTF-8') . "');";
        $studentSize = atenea_certificate_html_student_size((string) $data['student_name']);
        $titleSize = atenea_certificate_html_title_size((string) $data['certificate_name']);

        ob_start();
        ?>
        <div class="atenea-certificate" style="<?php echo $backgroundStyle; ?>">
          <div class="atenea-certificate__item atenea-certificate__school"><?php echo $institution; ?></div>
          <div class="atenea-certificate__item atenea-certificate__label">CERTIFICA A:</div>
          <div class="atenea-certificate__item atenea-certificate__student" style="font-size: <?php echo htmlspecialchars($studentSize, ENT_QUOTES, 'UTF-8'); ?>;">
            <?php echo $studentName; ?>
          </div>
          <div class="atenea-certificate__item atenea-certificate__body">
            Estudiante que cumplió los requisitos exigidos para el <strong>CERTIFICADO</strong> de
          </div>
          <div class="atenea-certificate__item atenea-certificate__title" style="font-size: <?php echo htmlspecialchars($titleSize, ENT_QUOTES, 'UTF-8'); ?>;">
            <?php echo $certificateName; ?>
          </div>
          <div class="atenea-certificate__item atenea-certificate__subtitle"><?php echo $subtitle; ?></div>
          <div class="atenea-certificate__item atenea-certificate__signature atenea-certificate__signature--left">
            <strong><?php echo $presidentName; ?></strong>
            <?php echo $presidentRole; ?>
          </div>
          <div class="atenea-certificate__item atenea-certificate__signature atenea-certificate__signature--right">
            <strong><?php echo $facilitatorName; ?></strong>
            <?php echo $facilitatorRole; ?>
          </div>
          <div class="atenea-certificate__item atenea-certificate__support"><?php echo $supportText; ?></div>
          <div class="atenea-certificate__item atenea-certificate__date">
            <?php echo $location; ?> a los <?php echo $day; ?> días del mes de <?php echo $month; ?> de <?php echo $year; ?>
          </div>
        </div>
        <?php

        return (string) ob_get_clean();
    }
}

if (!function_exists('atenea_certificate_pdf_mm')) {
    function atenea_certificate_pdf_mm(float $pixels): float
    {
        return $pixels / 4.0;
    }
}

if (!function_exists('atenea_certificate_pdf_text')) {
    function atenea_certificate_pdf_text(
        TCPDF $pdf,
        string $font,
        float $fontSize,
        array $color,
        float $x,
        float $y,
        float $width,
        float $height,
        string $text,
        string $align = 'C',
        float $spacing = 0.0
    ): void {
        $pdf->SetTextColor((int) $color[0], (int) $color[1], (int) $color[2]);
        $pdf->SetFont($font, '', $fontSize);
        $pdf->setFontSpacing($spacing);
        $pdf->SetXY(atenea_certificate_pdf_mm($x), atenea_certificate_pdf_mm($y));
        $pdf->MultiCell(atenea_certificate_pdf_mm($width), atenea_certificate_pdf_mm($height), $text, 0, $align, false, 1);
        $pdf->setFontSpacing(0);
    }
}

if (!function_exists('atenea_certificate_pdf_binary')) {
    function atenea_certificate_pdf_binary(array $data): string
    {
        atenea_certificate_bootstrap();

        $data = atenea_certificate_build_data($data);
        $fonts = atenea_certificate_pdf_fonts();
        $backgroundPath = atenea_certificate_background_absolute_path();

        $pdf = new TCPDF('L', 'mm', [216, 288], true, 'UTF-8', false);
        $pdf->SetCreator('Atenea');
        $pdf->SetAuthor('Atenea');
        $pdf->SetTitle('Certificado - ' . atenea_certificate_upper((string) $data['certificate_name']));
        $pdf->SetPrintHeader(false);
        $pdf->SetPrintFooter(false);
        $pdf->SetMargins(0, 0, 0);
        $pdf->SetAutoPageBreak(false, 0);
        $pdf->AddPage();

        if ($backgroundPath !== null) {
            $pdf->Image($backgroundPath, 0, 0, 288, 216, '', '', '', false, 300, '', false, false, 0, false, false, false);
        } else {
            $pdf->SetFillColor(247, 238, 244);
            $pdf->Rect(0, 0, 288, 216, 'F');
        }

        atenea_certificate_pdf_text(
            $pdf,
            $fonts['school'],
            23.5,
            [207, 138, 141],
            250,
            100,
            790,
            68,
            (string) $data['institution_name'],
            'C'
        );

        atenea_certificate_pdf_text(
            $pdf,
            $fonts['body_bold'],
            11.2,
            [193, 127, 119],
            508,
            182,
            178,
            32,
            'CERTIFICA A:',
            'C'
        );

        atenea_certificate_pdf_text(
            $pdf,
            $fonts['student'],
            atenea_certificate_pdf_student_size((string) $data['student_name']),
            [78, 77, 112],
            300,
            218,
            670,
            78,
            (string) $data['student_name'],
            'C'
        );

        atenea_certificate_pdf_text(
            $pdf,
            $fonts['body_bold'],
            11.3,
            [50, 44, 53],
            110,
            355,
            925,
            34,
            'Estudiante que cumplió los requisitos exigidos para el CERTIFICADO de',
            'C'
        );

        atenea_certificate_pdf_text(
            $pdf,
            $fonts['title'],
            atenea_certificate_pdf_title_size((string) $data['certificate_name']),
            [215, 129, 149],
            245,
            408,
            662,
            58,
            atenea_certificate_upper((string) $data['certificate_name']),
            'C',
            1.25
        );

        atenea_certificate_pdf_text(
            $pdf,
            $fonts['body'],
            14.0,
            [59, 53, 66],
            255,
            492,
            650,
            36,
            (string) $data['certificate_subtitle'],
            'C',
            0.2
        );

        atenea_certificate_pdf_text(
            $pdf,
            $fonts['body_bold'],
            10.9,
            [47, 40, 52],
            242,
            578,
            275,
            30,
            (string) $data['president_name'],
            'C'
        );

        atenea_certificate_pdf_text(
            $pdf,
            $fonts['body'],
            10.5,
            [47, 40, 52],
            260,
            614,
            240,
            26,
            (string) $data['president_role'],
            'C'
        );

        atenea_certificate_pdf_text(
            $pdf,
            $fonts['body_bold'],
            10.9,
            [47, 40, 52],
            575,
            578,
            285,
            30,
            (string) $data['facilitator_name'],
            'C'
        );

        atenea_certificate_pdf_text(
            $pdf,
            $fonts['body'],
            10.5,
            [47, 40, 52],
            610,
            614,
            216,
            26,
            (string) $data['facilitator_role'],
            'C'
        );

        atenea_certificate_pdf_text(
            $pdf,
            $fonts['body'],
            9.2,
            [63, 55, 68],
            135,
            661,
            545,
            24,
            (string) $data['cooperative_text'],
            'C'
        );

        atenea_certificate_pdf_text(
            $pdf,
            $fonts['body_bold'],
            10.9,
            [63, 55, 68],
            282,
            706,
            620,
            28,
            (string) $data['location'] . ' a los ' . (string) $data['day'] . ' días del mes de ' . (string) $data['month'] . ' de ' . (string) $data['year'],
            'C'
        );

        return (string) $pdf->Output('', 'S');
    }
}
