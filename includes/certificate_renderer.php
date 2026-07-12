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

if (!function_exists('atenea_certificate_upper')) {
    function atenea_certificate_upper(string $value): string
    {
        return function_exists('mb_strtoupper')
            ? mb_strtoupper($value, 'UTF-8')
            : strtoupper($value);
    }
}

if (!function_exists('atenea_certificate_slug')) {
    function atenea_certificate_slug(string $value): string
    {
        $value = trim($value);

        if ($value === '') {
            return '';
        }

        if (function_exists('iconv')) {
            $transliterated = @iconv('UTF-8', 'ASCII//TRANSLIT//IGNORE', $value);
            if ($transliterated !== false) {
                $value = $transliterated;
            }
        }

        $value = strtolower($value);
        $value = preg_replace('/[^a-z0-9]+/', '-', $value) ?? '';

        return trim($value, '-');
    }
}

if (!function_exists('atenea_certificate_default_data')) {
    function atenea_certificate_default_data(): array
    {
        $monthNames = array_values(atenea_certificate_months());

        return [
            'student_name' => 'Reynalda Davila B.',
            'certificate_name' => 'LIMPIEZA DE OIDOS',
            'certificate_subtitle' => 'Ear Candling - Conoterapia Nivel I',
            'location' => 'San Salvador',
            'day' => (string) date('j'),
            'month' => $monthNames[(int) date('n') - 1] ?? 'julio',
            'year' => (string) date('Y'),
            'certificate_code' => '',
            'template_file' => '',
            'institution_name' => 'Escuela de Naturopatia Holistica',
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
        $data['certificate_subtitle'] = atenea_certificate_clean_text((string) ($input['certificate_subtitle'] ?? $defaults['certificate_subtitle']), 180);
        $data['location'] = atenea_certificate_clean_text((string) ($input['location'] ?? $defaults['location']), 80);
        $data['certificate_code'] = atenea_certificate_clean_text((string) ($input['certificate_code'] ?? $defaults['certificate_code']), 40);
        $data['template_file'] = basename(atenea_certificate_clean_text((string) ($input['template_file'] ?? $defaults['template_file']), 160));
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
            'certificate_code' => (string) ($data['certificate_code'] ?? ''),
            'template_file' => (string) ($data['template_file'] ?? ''),
            'institution_name' => (string) ($data['institution_name'] ?? ''),
            'president_name' => (string) ($data['president_name'] ?? ''),
            'president_role' => (string) ($data['president_role'] ?? ''),
            'facilitator_name' => (string) ($data['facilitator_name'] ?? ''),
            'facilitator_role' => (string) ($data['facilitator_role'] ?? ''),
            'cooperative_text' => (string) ($data['cooperative_text'] ?? ''),
        ];
    }
}

if (!function_exists('atenea_certificate_template_catalog')) {
    function atenea_certificate_template_catalog(): array
    {
        return [
            'generic' => [
                'key' => 'generic',
                'filename' => 'certificado_generico_20260709.png',
                'show_course_title' => true,
                'show_course_subtitle' => true,
            ],
            'limpieza-de-oidos' => [
                'key' => 'limpieza-de-oidos',
                'filename' => 'certificado_limpieza_oidos_20260709.png',
                'show_course_title' => false,
                'show_course_subtitle' => false,
            ],
        ];
    }
}

if (!function_exists('atenea_certificate_template_absolute_path')) {
    function atenea_certificate_template_absolute_path(string $filename): ?string
    {
        $path = __DIR__ . '/../img/certificados/' . basename($filename);

        return is_file($path) ? $path : null;
    }
}

if (!function_exists('atenea_certificate_resolve_template')) {
    function atenea_certificate_resolve_template(array $data = []): array
    {
        $data = atenea_certificate_build_data($data);
        $catalog = atenea_certificate_template_catalog();

        $buildTemplate = static function (string $key, string $filename, bool $showCourseTitle, bool $showCourseSubtitle): array {
            $absolutePath = atenea_certificate_template_absolute_path($filename);
            $version = $absolutePath !== null ? (string) (@filemtime($absolutePath) ?: 0) : '0';

            return [
                'key' => $key,
                'filename' => $filename,
                'absolute_path' => $absolutePath,
                'relative_path' => '../img/certificados/' . $filename,
                'relative_url' => '../img/certificados/' . $filename . '?v=' . rawurlencode($version),
                'version' => $version,
                'show_course_title' => $showCourseTitle,
                'show_course_subtitle' => $showCourseSubtitle,
            ];
        };

        $explicitTemplateFile = trim((string) ($data['template_file'] ?? ''));
        if ($explicitTemplateFile !== '') {
            foreach ($catalog as $templateMeta) {
                if ($explicitTemplateFile === (string) $templateMeta['filename']) {
                    return $buildTemplate(
                        (string) $templateMeta['key'],
                        (string) $templateMeta['filename'],
                        !empty($templateMeta['show_course_title']),
                        !empty($templateMeta['show_course_subtitle'])
                    );
                }
            }

            if (atenea_certificate_template_absolute_path($explicitTemplateFile) !== null) {
                return $buildTemplate('custom-explicit', $explicitTemplateFile, false, false);
            }
        }

        $courseSlug = atenea_certificate_slug((string) ($data['certificate_name'] ?? ''));
        if ($courseSlug !== '' && isset($catalog[$courseSlug])) {
            $templateMeta = $catalog[$courseSlug];

            return $buildTemplate(
                (string) $templateMeta['key'],
                (string) $templateMeta['filename'],
                !empty($templateMeta['show_course_title']),
                !empty($templateMeta['show_course_subtitle'])
            );
        }

        if ($courseSlug !== '') {
            $customCandidates = [
                'certificado_' . str_replace('-', '_', $courseSlug) . '.png',
                'certificado_' . str_replace('-', '_', $courseSlug) . '.jpg',
                'certificado_' . str_replace('-', '_', $courseSlug) . '.jpeg',
                'plantilla_' . str_replace('-', '_', $courseSlug) . '.png',
                'plantilla_' . str_replace('-', '_', $courseSlug) . '.jpg',
                'plantilla_' . str_replace('-', '_', $courseSlug) . '.jpeg',
            ];

            foreach ($customCandidates as $candidate) {
                if (atenea_certificate_template_absolute_path($candidate) !== null) {
                    return $buildTemplate('custom-course', $candidate, false, false);
                }
            }
        }

        $generic = $catalog['generic'];

        return $buildTemplate(
            (string) $generic['key'],
            (string) $generic['filename'],
            !empty($generic['show_course_title']),
            !empty($generic['show_course_subtitle'])
        );
    }
}

if (!function_exists('atenea_certificate_template_version')) {
    function atenea_certificate_template_version(array $data = []): string
    {

        return (string) ($template['version'] ?? '0');
    }
}

if (!function_exists('atenea_certificate_html_student_size')) {
    function atenea_certificate_html_student_size(string $studentName): string
    {
        $length = atenea_certificate_length($studentName);

        if ($length <= 18) {
            return '5.5rem';
        }

        if ($length <= 28) {
            return '4.8rem';
        }

        if ($length <= 38) {
            return '4rem';
        }

        return '3.35rem';
    }
}

if (!function_exists('atenea_certificate_html_title_size')) {
    function atenea_certificate_html_title_size(string $title): string
    {
        $length = atenea_certificate_length($title);

        if ($length <= 20) {
            return '3.45rem';
        }

        if ($length <= 30) {
            return '3rem';
        }

        if ($length <= 42) {
            return '2.45rem';
        }

        return '2.05rem';
    }
}

if (!function_exists('atenea_certificate_pdf_student_size')) {
    function atenea_certificate_pdf_student_size(string $studentName): float
    {
        $length = atenea_certificate_length($studentName);

        if ($length <= 18) {
            return 28.0;
        }

        if ($length <= 28) {
            return 24.5;
        }

        if ($length <= 38) {
            return 21.5;
        }

        return 18.5;
    }
}

if (!function_exists('atenea_certificate_pdf_title_size')) {
    function atenea_certificate_pdf_title_size(string $title): float
    {
        $length = atenea_certificate_length($title);

        if ($length <= 20) {
            return 19.0;
        }

        if ($length <= 30) {
            return 16.5;
        }

        if ($length <= 42) {
            return 14.4;
        }

        return 12.4;
    }
}

if (!function_exists('atenea_certificate_windows_font')) {
    function atenea_certificate_windows_font(array $candidates): ?string
    {
        $fontDirs = [
            getenv('WINDIR') . DIRECTORY_SEPARATOR . 'Fonts',
            'C:\\Windows\\Fonts',
        ];

        foreach ($fontDirs as $fontDir) {
            foreach ($candidates as $candidate) {
                $path = $fontDir . DIRECTORY_SEPARATOR . $candidate;
                if (is_file($path)) {
                    return $path;
                }
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

.atenea-certificate__field {
  position: absolute;
  z-index: 2;
  text-align: center;
  text-shadow: 0 1px 0 rgba(255, 255, 255, 0.45);
}

.atenea-certificate__student {
  left: 25.694444%;
  top: 25.347222%;
  width: 58.333333%;
  color: #4e4d70;
  font-family: "Monotype Corsiva", "French Script MT", "Brush Script MT", cursive;
  line-height: 1;
  padding: 0 0.75rem;
}

.atenea-certificate__course-title {
  left: 21.267361%;
  top: 47.222222%;
  width: 57.638889%;
  color: #d63f47;
  font-family: Garamond, "Palatino Linotype", "Book Antiqua", serif;
  font-weight: 700;
  letter-spacing: 0.14em;
  line-height: 1.05;
  text-transform: uppercase;
  padding: 0 0.5rem;
}

.atenea-certificate__course-subtitle {
  left: 22.135417%;
  top: 57.037037%;
  width: 55.989583%;
  color: #342f40;
  font-family: Garamond, "Palatino Linotype", "Book Antiqua", serif;
  font-size: 2.1rem;
  line-height: 1.08;
  letter-spacing: 0.03em;
  padding: 0 0.5rem;
}

.atenea-certificate__date {
  color: #3f3744;
  font-family: Garamond, "Palatino Linotype", "Book Antiqua", serif;
  font-size: 1.35rem;
  font-weight: 700;
  line-height: 1;
  white-space: nowrap;
}

.atenea-certificate__date--day {
  left: 39.613526%;
  top: 86.648250%;
  width: 5.175983%;
}

.atenea-certificate__date--month {
  left: 57.901311%;
  top: 86.648250%;
  width: 16.563147%;
}

.atenea-certificate__date--year {
  left: 74.464458%;
  top: 86.648250%;
  width: 7.660455%;
}

.atenea-certificate__code {
  right: 5.5%;
  bottom: 5.2%;
  color: #5b5962;
  font-family: Garamond, "Palatino Linotype", "Book Antiqua", serif;
  font-size: 0.82rem;
  letter-spacing: 0.08em;
}

@media (max-width: 1199.98px) {
  .atenea-certificate__course-subtitle {
    font-size: 1.7rem;
  }

  .atenea-certificate__date {
    font-size: 1.08rem;
  }
}

@media (max-width: 767.98px) {
  .atenea-certificate-preview-wrap {
    padding: 0.75rem;
  }

  .atenea-certificate__course-subtitle {
    font-size: 1.05rem;
  }

  .atenea-certificate__date {
    font-size: 0.78rem;
  }

  .atenea-certificate__code {
    font-size: 0.62rem;
  }
}
CSS;
    }
}

if (!function_exists('atenea_certificate_html')) {
    function atenea_certificate_html(array $data): string
    {
        $data = atenea_certificate_build_data($data);
        $template = atenea_certificate_resolve_template($data);
        $studentName = htmlspecialchars((string) $data['student_name'], ENT_QUOTES, 'UTF-8');
        $certificateName = htmlspecialchars(atenea_certificate_upper((string) $data['certificate_name']), ENT_QUOTES, 'UTF-8');
        $subtitle = htmlspecialchars((string) $data['certificate_subtitle'], ENT_QUOTES, 'UTF-8');
        $day = htmlspecialchars((string) $data['day'], ENT_QUOTES, 'UTF-8');
        $month = htmlspecialchars((string) $data['month'], ENT_QUOTES, 'UTF-8');
        $year = htmlspecialchars((string) $data['year'], ENT_QUOTES, 'UTF-8');
        $certificateCode = htmlspecialchars((string) $data['certificate_code'], ENT_QUOTES, 'UTF-8');
        $studentSize = atenea_certificate_html_student_size((string) $data['student_name']);
        $titleSize = atenea_certificate_html_title_size((string) $data['certificate_name']);

        ob_start();
        ?>
        <div class="atenea-certificate atenea-certificate--clean">
          <div class="atenea-certificate__brand">ATENEA</div>
          <div class="atenea-certificate__heading">CERTIFICADO DE APROBACION</div>
          <div class="atenea-certificate__intro">Se otorga el presente certificado a</div>
          <div class="atenea-certificate__field atenea-certificate__student" style="font-size: <?php echo htmlspecialchars($studentSize, ENT_QUOTES, 'UTF-8'); ?>;">
            <?php echo $studentName; ?>
          </div>
            <div class="atenea-certificate__field atenea-certificate__course-title" style="font-size: <?php echo htmlspecialchars($titleSize, ENT_QUOTES, 'UTF-8'); ?>;">
              <?php echo $certificateName; ?>
            </div>
          <div class="atenea-certificate__achievement">por haber completado y aprobado satisfactoriamente el curso</div>
          <?php if (trim((string) $data['certificate_subtitle']) !== '') : ?>
            <div class="atenea-certificate__field atenea-certificate__course-subtitle">
              <?php echo $subtitle; ?>
            </div>
          <?php endif; ?>
          <div class="atenea-certificate__date-line">Emitido el <?php echo $day; ?> de <?php echo $month; ?> de <?php echo $year; ?></div>
          <div class="atenea-certificate__signature"><span></span><strong>Direccion Academica</strong></div>
          <?php if ($certificateCode !== '') : ?>
            <div class="atenea-certificate__field atenea-certificate__code"><?php echo $certificateCode; ?></div>
          <?php endif; ?>
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

        $pdf = new TCPDF('L', 'mm', [216, 288], true, 'UTF-8', false);
        $pdf->SetCreator('Atenea');
        $pdf->SetAuthor('Atenea');
        $pdf->SetTitle('Certificado - ' . atenea_certificate_upper((string) $data['certificate_name']));
        $pdf->SetPrintHeader(false);
        $pdf->SetPrintFooter(false);
        $pdf->SetMargins(0, 0, 0);
        $pdf->SetAutoPageBreak(false, 0);
        $pdf->AddPage();

        $pdf->SetFillColor(250,248,241); $pdf->Rect(0,0,288,216,'F');
        $pdf->SetDrawColor(4,104,69); $pdf->SetLineWidth(2); $pdf->Rect(8,8,272,200);
        $pdf->SetDrawColor(200,161,51); $pdf->SetLineWidth(.7); $pdf->Rect(12,12,264,192);
        $pdf->SetTextColor(4,104,69); $pdf->SetFont($fonts['title'],'',24); $pdf->SetXY(20,24); $pdf->Cell(248,12,'ATENEA',0,1,'C');
        $pdf->SetTextColor(13,36,56); $pdf->SetFont($fonts['title'],'',17); $pdf->SetXY(20,43); $pdf->Cell(248,10,'CERTIFICADO DE APROBACION',0,1,'C');
        $pdf->SetFont($fonts['body'],'',11); $pdf->SetXY(20,62); $pdf->Cell(248,8,'Se otorga el presente certificado a',0,1,'C');
        $pdf->SetTextColor(4,104,69); $pdf->SetFont($fonts['student'],'',atenea_certificate_pdf_student_size((string)$data['student_name'])); $pdf->SetXY(28,75); $pdf->MultiCell(232,18,(string)$data['student_name'],0,'C');
        $pdf->SetTextColor(13,36,56); $pdf->SetFont($fonts['body'],'',10); $pdf->SetXY(25,101); $pdf->Cell(238,7,'por haber completado y aprobado satisfactoriamente el curso',0,1,'C');
        $pdf->SetTextColor(160,125,35); $pdf->SetFont($fonts['title'],'',atenea_certificate_pdf_title_size((string)$data['certificate_name'])); $pdf->SetXY(28,112); $pdf->MultiCell(232,16,atenea_certificate_upper((string)$data['certificate_name']),0,'C');
        if (trim((string)$data['certificate_subtitle']) !== '') { $pdf->SetTextColor(70,80,76); $pdf->SetFont($fonts['body'],'',9); $pdf->SetXY(35,137); $pdf->MultiCell(218,10,(string)$data['certificate_subtitle'],0,'C'); }
        $pdf->SetTextColor(13,36,56); $pdf->SetFont($fonts['body'],'',9); $pdf->SetXY(25,163); $pdf->Cell(238,7,'Emitido el '.$data['day'].' de '.$data['month'].' de '.$data['year'],0,1,'C');
        $pdf->Line(104,184,184,184); $pdf->SetFont($fonts['body_bold'],'',9); $pdf->SetXY(94,185); $pdf->Cell(100,6,'Direccion Academica',0,1,'C');

        if (trim((string) $data['certificate_code']) !== '') {
            atenea_certificate_pdf_text(
                $pdf,
                $fonts['body'],
                7.8,
                [91, 89, 98],
                1120,
                1012,
                180,
                18,
                (string) $data['certificate_code'],
                'R'
            );
        }

        return (string) $pdf->Output('', 'S');
    }
}
