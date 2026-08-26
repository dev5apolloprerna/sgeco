<?php

/**
 * Right-align values whose register heading explicitly identifies an amount.
 *
 * The alignment is applied to body cells only, so report titles, headings and
 * every non-amount column retain their existing layout.
 */
function rightAlignOtherReportAmountColumns($html)
{
    $document = new DOMDocument();
    $previous = libxml_use_internal_errors(true);
    if (!$document->loadHTML('<?xml encoding="UTF-8">' . $html)) {
        libxml_clear_errors();
        libxml_use_internal_errors($previous);
        return $html;
    }
    libxml_clear_errors();
    libxml_use_internal_errors($previous);

    $xpath = new DOMXPath($document);
    foreach ($xpath->query('//table[.//th and .//tr[contains(concat(" ", normalize-space(@class), " "), " data-row ")]]') as $table) {
        $amountColumns = array();
        $occupied = array();
        foreach ($xpath->query('.//tr[th]', $table) as $headingRow) {
            foreach ($occupied as $column => $remainingRows) {
                if ($remainingRows <= 1) {
                    unset($occupied[$column]);
                } else {
                    $occupied[$column]--;
                }
            }

            $column = 0;
            foreach ($xpath->query('./th', $headingRow) as $heading) {
                while (isset($occupied[$column])) {
                    $column++;
                }
                $columnSpan = max(1, (int) $heading->getAttribute('colspan'));
                $rowSpan = max(1, (int) $heading->getAttribute('rowspan'));
                for ($offset = 0; $offset < $columnSpan; $offset++) {
                    if ($rowSpan > 1) {
                        $occupied[$column + $offset] = $rowSpan;
                    }
                    if (preg_match('/\b(?:amt|amount)\.?\b/i', trim($heading->textContent))) {
                        $amountColumns[$column + $offset] = true;
                    }
                }
                $column += $columnSpan;
            }
        }

        foreach ($xpath->query('.//tr[contains(concat(" ", normalize-space(@class), " "), " data-row ")]', $table) as $row) {
            $column = 0;
            foreach ($xpath->query('./td', $row) as $cell) {
                $span = max(1, (int) $cell->getAttribute('colspan'));
                if (isset($amountColumns[$column])) {
                    $cell->setAttribute('align', 'right');
                    $style = rtrim(trim($cell->getAttribute('style')), ';');
                    $cell->setAttribute('style', ($style === '' ? '' : $style . ';') . 'text-align:right');
                }
                $column += $span;
            }
        }
    }

    $result = $document->saveHTML();
    $result = preg_replace('/^<\?xml encoding="UTF-8">\s*/', '', $result);
    return $result;
}

/**
 * Prepare only the register table heading for TCPDF continuation pages.
 *
 * TCPDF repeats THEAD after an automatic page break. The statutory column
 * numbers are kept in TBODY by the browser templates, so move that one row
 * into the register table's THEAD for PDF output. Scoping the operation to a
 * direct child of .register-table prevents headings from layout or nested
 * tables from becoming continuation-page headers.
 */
function configureOtherReportPdfTableHeader($html, $repeatTableHeaders)
{
    $document = new DOMDocument();
    $previous = libxml_use_internal_errors(true);
    if (!$document->loadHTML('<?xml encoding="UTF-8">' . $html)) {
        libxml_clear_errors();
        libxml_use_internal_errors($previous);
        return $html;
    }
    libxml_clear_errors();
    libxml_use_internal_errors($previous);

    $xpath = new DOMXPath($document);
    $tables = $xpath->query(
        '//table[contains(concat(" ", normalize-space(@class), " "), " register-table ")]'
    );
    foreach ($tables as $table) {
        $headings = $xpath->query('./thead[1]', $table);
        if ($headings->length === 0) {
            continue;
        }
        $heading = $headings->item(0);

        if ($repeatTableHeaders) {
            $numberRows = $xpath->query(
                './tbody[1]/tr[1][contains(concat(" ", normalize-space(@class), " "), " column-number-row ")]',
                $table
            );
            if ($numberRows->length > 0) {
                $heading->appendChild($numberRows->item(0));
            }
            continue;
        }

        // Replacing THEAD with TBODY disables TCPDF's automatic repetition
        // without changing any heading cells or their visual formatting.
        $body = $document->createElement('tbody');
        while ($heading->firstChild) {
            $body->appendChild($heading->firstChild);
        }
        $heading->parentNode->replaceChild($body, $heading);
    }

    $result = $document->saveHTML();
    return preg_replace('/^<\?xml encoding="UTF-8">\s*/', '', $result);
}

/**
 * Add export-only table spacing without changing the browser report templates.
 *
 * TCPDF honours cell padding and line-height more reliably than CSS min-height.
 * Letting the cells grow with their content also prevents wrapped names from
 * overlapping the following row.
 */
function addOtherReportPdfSpacing($html, $repeatTableHeaders = true, $cellPadding = '5px 3px')
{
    $html = rightAlignOtherReportAmountColumns($html);
    $cellPadding = htmlspecialchars($cellPadding, ENT_QUOTES, 'UTF-8');
    $style = '<style type="text/css">'
        . '.register-table tbody.register-body td,'
        . '.register-table tbody .data-row td {'
        . 'padding: ' . $cellPadding . '; line-height: 1.25; vertical-align: middle;'
        . '}'
        . '.register-table tbody .data-row td.employee-text {'
        . 'padding-left: 7px;'
        . '}'
        . '</style>';

    // TCPDF can split a table row between pages when the remaining page
    // height is very small. Keeping each employee row intact prevents the
    // final row on one page from overlapping the first row on the next.
    $html = preg_replace(
        '/<tr\s+class=("|\')data-row\1(?![^>]*\bnobr=)[^>]*>/i',
        '<tr class="data-row" nobr="true">',
        $html
    );

    // The supplied register templates keep the statutory column-number row at
    // the start of TBODY. For PDF output it is part of the table heading too:
    // move it into the preceding THEAD so TCPDF repeats the complete table
    // heading (labels and column numbers), but not the form/establishment
    // details, after an automatic page break.
    $html = configureOtherReportPdfTableHeader($html, $repeatTableHeaders);
    if (stripos($html, '</head>') !== false) {
        return preg_replace('/<\/head>/i', $style . '</head>', $html, 1);
    }

    return $style . $html;
}

/**
 * Split a report containing repeated form sections into standalone documents.
 *
 * Browser print engines honour the CSS break-before rule on .form-page, but
 * TCPDF does not. Passing all sections to one writeHTML() call therefore lets
 * the next form header begin in the unused space at the bottom of the current
 * PDF page. Keeping the document head on every fragment preserves its styles
 * while allowing the PDF generator to add an explicit page for each section.
 */
function splitOtherReportPdfPages($html)
{
    if (!preg_match('/\A(.*?<body\b[^>]*>)(.*)(<\/body>\s*<\/html>\s*)\z/is', $html, $document)) {
        return array($html);
    }

    if (!preg_match_all(
        '/<section\b[^>]*class\s*=\s*(["\'])[^"\']*\bform-page\b[^"\']*\1[^>]*>.*?<\/section>/is',
        $document[2],
        $sections
    ) || count($sections[0]) < 2) {
        return array($html);
    }

    $pages = array();
    foreach ($sections[0] as $section) {
        $pages[] = $document[1] . $section . $document[3];
    }
    return $pages;
}

/**
 * Keep the Form XX PDF header faithful to the printed register.
 *
 * TCPDF does not apply the browser's table layout in exactly the same way and
 * can inherit bold text or wrap labels which fit on one line in the template.
 * These export-only rules deliberately leave only the marked labels bold and
 * reserve a little more horizontal room for the two detail columns.
 */
function addFormXXPdfFormatting($html)
{
    // Keep every row on the same grid as the template header. Different body
    // widths make TCPDF draw vertical borders at different positions.
    $columnWidths = array(3, 18, 17, 8, 6, 6, 6, 6, 6, 6, 6, 6, 6);

    // TCPDF ignores <colgroup> widths in some releases. Once that happens it
    // gives the body cells an automatic width and the final cells can fall
    // outside the row (columns 12 and 13 were consequently blank). Put the
    // same percentages on every numbered/data cell so all thirteen columns
    // retain the supplied layout and remain inside the printable table.
    $html = preg_replace_callback(
        '/<tr\b[^>]*class=("|\')(?:column-number-row|data-row)\1[^>]*>.*?<\/tr>/is',
        function ($rowMatch) use ($columnWidths) {
            $columnIndex = 0;
            return preg_replace_callback(
                '/<(td|th)\b([^>]*)>/i',
                function ($cellMatch) use (&$columnIndex, $columnWidths) {
                    if (!isset($columnWidths[$columnIndex])) {
                        return $cellMatch[0];
                    }
                    $width = $columnWidths[$columnIndex++];
                    return '<' . $cellMatch[1] . ' width="' . $width . '%"' . $cellMatch[2] . '>';
                },
                $rowMatch[0]
            );
        },
        $html
    );

    // The first heading row establishes the TCPDF table grid. Its recovery
    // heading spans columns 11 and 12, so it receives their combined width.
    $headingWidths = array(3, 18, 17, 8, 6, 6, 6, 6, 6, 6, 12, 6);
    $html = preg_replace_callback(
        '/(<thead\b[^>]*>\s*)<tr\b([^>]*)>(.*?)<\/tr>/is',
        function ($rowMatch) use ($headingWidths) {
            $columnIndex = 0;
            $cells = preg_replace_callback(
                '/<th\b([^>]*)>/i',
                function ($cellMatch) use (&$columnIndex, $headingWidths) {
                    if (!isset($headingWidths[$columnIndex])) {
                        return $cellMatch[0];
                    }
                    $width = $headingWidths[$columnIndex++];
                    return '<th width="' . $width . '%"' . $cellMatch[1] . '>';
                },
                $rowMatch[3]
            );
            return $rowMatch[1] . '<tr' . $rowMatch[2] . '>' . $cells . '</tr>';
        },
        $html,
        1
    );

    // The two recovery subheadings are emitted in a separate row, so TCPDF
    // cannot reliably infer their widths from the colspan in the row above.
    $html = preg_replace_callback(
        '/(<thead\b[^>]*>.*?<\/tr>\s*)<tr\b([^>]*)>(.*?)<\/tr>/is',
        function ($rowMatch) {
            $cells = preg_replace(
                '/<th\b([^>]*)>/i',
                '<th width="6%"$1>',
                $rowMatch[3]
            );
            return $rowMatch[1] . '<tr' . $rowMatch[2] . '>' . $cells . '</tr>';
        },
        $html,
        1
    );

    // TCPDF's CSS cascade is limited.  Put the important header rules on the
    // elements themselves so a <strong> in one cell cannot make the remaining
    // header cells bold, and use its nowrap attribute for the long labels.
    $html = preg_replace(
        '/<td class="(normal-weight|address-line)"([^>]*)>/i',
        '<td class="$1"$2 style="font-weight: normal; white-space: nowrap;" nowrap="nowrap">',
        $html
    );
    $html = preg_replace(
        '/<div class="right-line"([^>]*)>/i',
        '<div class="right-line"$1 style="font-weight: normal; white-space: nowrap;">',
        $html
    );

    // A non-zero cellpadding is more consistently honoured by TCPDF than a
    // stylesheet rule alone.  The data-row rule below then adds the slightly
    // roomier vertical spacing used by the supplied register.
    $html = preg_replace(
        '/(<table class="register-table"[^>]*\bcellpadding=")[^"]*(")/i',
        '${1}4${2}',
        $html,
        1
    );
    $style = '<style type="text/css">'
        . '.header-table, .header-table td, .header-table div, .header-table span {'
        . 'font-weight: normal;'
        . '}'
         . '.header-table .info-label strong, .header-table .form-number strong,'
        . '.header-table .month-label strong, .header-table .right-line strong,'
        . '.main-title strong, .work-title {'
        . 'font-weight: bold;'
        . '}'
        . '.header-table .left-header { width: 41%; font-size: 9px; }'
        . '.header-table .center-header { width: 18%; }'
        . '.header-table .right-header { width: 41%; font-size: 9px; }'
        . '.header-table .info-label, .header-table .normal-weight,'
        . '.header-table .address-line, .header-table .right-line {'
        . 'white-space: nowrap;'
        . '}'
        . '.register-table tbody .data-row td {'
        . 'padding: 7px 5px; line-height: 1.35; vertical-align: middle;'
        . '}'
        . '.register-table tbody .data-row td.text-left {'
        . 'padding-left: 7px;'
        . '}'
        . '</style>';

    if (stripos($html, '</head>') !== false) {
        return preg_replace('/<\/head>/i', $style . '</head>', $html, 1);
    }

    return $style . $html;
}

/**
 * Keep the Form XXII PDF header on the same compact, single-line layout as
 * Form XX. These rules are export-only because TCPDF does not consistently
 * honour the template's colgroup widths or white-space declarations.
 */
function addFormXXIIPdfFormatting($html)
{
    // Explicit width attributes are more reliable than colgroup in TCPDF.
    $html = preg_replace(
        '/<td class="left-header"([^>]*)>/i',
        '<td class="left-header" width="41%"$1>',
        $html,
        1
    );
    $html = preg_replace(
        '/<td class="center-header"([^>]*)>/i',
        '<td class="center-header" width="18%"$1>',
        $html,
        1
    );
    $html = preg_replace(
        '/<td class="right-header"([^>]*)>/i',
        '<td class="right-header" width="41%"$1>',
        $html,
        1
    );

    // Match Form XX's contractor grid and prevent TCPDF from wrapping the
    // contractor and principal-employer lines despite their fitting widths.
    $html = preg_replace(
        '/<td class="contractor-label"([^>]*)>/i',
        '<td class="contractor-label" width="55%"$1 nowrap="nowrap">',
        $html,
        1
    );
    $html = preg_replace(
        '/(<td class="contractor-label"[^>]*>.*?<\/td>\s*)<td([^>]*)>/is',
        '$1<td width="45%"$2 nowrap="nowrap">',
        $html,
        1
    );
    $html = preg_replace(
        '/<div class="right-heading"([^>]*)>/i',
        '<div class="right-heading"$1 style="white-space: nowrap;">',
        $html
    );

    $style = '<style type="text/css">'
        . '.header-table .left-header { width: 41%; font-size: 9px; }'
        . '.header-table .center-header { width: 18%; }'
        . '.header-table .right-header { width: 41%; font-size: 9px; }'
        . '.header-table .contractor-table td, .header-table .right-heading {'
        . 'white-space: nowrap;'
        . '}'
        . '</style>';

    if (stripos($html, '</head>') !== false) {
        return preg_replace('/<\/head>/i', $style . '</head>', $html, 1);
    }

    return $style . $html;
}

/**
 * Give the Form XXI PDF the same compact header proportions as Forms XX and
 * XXII without changing the browser report template.
 */
function addFormXXIPdfFormatting($html)
{
    $headerWidths = array(
        'left-header' => '41%',
        'center-header' => '18%',
        'right-header' => '41%'
    );

    foreach ($headerWidths as $className => $width) {
        $html = preg_replace_callback(
            '/<td class="' . preg_quote($className, '/') . '"([^>]*)>/i',
            function ($match) use ($className, $width) {
                $attributes = preg_replace('/\s+width="[^"]*"/i', '', $match[1]);
                $attributes = preg_replace_callback(
                    '/style="([^"]*)"/i',
                    function ($styleMatch) use ($width) {
                        $declarations = preg_replace('/(?:^|\s)width\s*:\s*[^;]+;?/i', '', $styleMatch[1]);
                        return 'style="width: ' . $width . '; ' . ltrim($declarations) . '"';
                    },
                    $attributes,
                    1
                );
                return '<td class="' . $className . '" width="' . $width . '"' . $attributes . '>';
            },
            $html
        );
    }

    // The template already defines the contractor grid. Add TCPDF's nowrap
    // attribute to its value and address cells, and keep both right-side
    // headings (including the principal employer value) together.
    $html = preg_replace(
        '/<td width="45%"([^>]*)>/i',
        '<td width="45%"$1 nowrap="nowrap">',
        $html,
        1
    );
    $html = preg_replace(
        '/<td class="address-line"([^>]*)>/i',
        '<td class="address-line"$1 nowrap="nowrap">',
        $html
    );
    $html = preg_replace(
        '/<div class="right-line"([^>]*)>/i',
        '<div class="right-line"$1 style="white-space: nowrap;">',
        $html
    );

    $style = '<style type="text/css">'
        . '.header-table .left-header { width: 41%; font-size: 9px; }'
        . '.header-table .center-header { width: 18%; }'
        . '.header-table .right-header { width: 41%; font-size: 9px; }'
        . '.header-table .info-table td, .header-table .right-line {'
        . 'white-space: nowrap;'
        . '}'
        . '</style>';

    if (stripos($html, '</head>') !== false) {
        return preg_replace('/<\/head>/i', $style . '</head>', $html, 1);
    }

    return $style . $html;
}

/**
 * Keep the Form C establishment and principal-employer details on the same
 * compact header line when TCPDF renders the report.
 */
function addFormCPdfFormatting($html)
{
    // Form C rows are deliberately single-line (the employee name is nowrap),
    // so they do not need TCPDF's transactional `nobr` handling.  When a
    // nobr row is the first row moved to an automatic continuation page,
    // TCPDF restores the row at the cell's padded X position instead of the
    // table's X position.  The repeated THEAD then starts at the correct left
    // edge while every body column is shifted to the right.  Remove that
    // shared-export safeguard for this fixed-height register before rendering.
    $html = preg_replace(
        '/(<tr\s+class=("|\')data-row\2)\s+nobr=("|\')true\3/i',
        '$1',
        $html
    );

    // Give TCPDF an explicit table width and zero HTML cell spacing. CSS-only
    // table sizing can be recalculated when THEAD is repeated, causing the
    // continuation-page heading to be a few pixels wider than its body.
    $html = preg_replace(
        '/<table class="register-table"([^>]*)>/i',
        '<table class="register-table" width="100%" cellspacing="0" cellpadding="0"$1>',
        $html,
        1
    );

    // Only the register's column headings belong in THEAD. TCPDF repeats this
    // row group after an automatic page break while leaving the legal form
    // title and establishment details on the first page.
    $html = preg_replace(
        '/<tbody class="register-heading">(.*?)<\/tbody>/is',
        '<thead class="register-heading">$1</thead>',
        $html,
        1
    );

    
    // A repeated THEAD is laid out independently by TCPDF on every new page.
    // If only its first row has widths, TCPDF can recalculate the numbered row
    // as thirteen equal columns and shift the headings away from the body
    // grid. Stamp the statutory 6/28/6... grid on both heading rows so every
    // continuation page starts at exactly the same column boundaries.
    $registerColumnWidths = array(6, 28, 6, 6, 6, 6, 6, 6, 6, 6, 6, 6, 6);
    $html = preg_replace_callback(
        '/<thead class="register-heading">(.*?)<\/thead>/is',
        function ($headingMatch) use ($registerColumnWidths) {
            $heading = preg_replace_callback(
                '/<tr\b([^>]*)>(.*?)<\/tr>/is',
                function ($rowMatch) use ($registerColumnWidths) {
                    $columnIndex = 0;
                    $cells = preg_replace_callback(
                        '/<th\b([^>]*)>/i',
                        function ($cellMatch) use (&$columnIndex, $registerColumnWidths) {
                            if (!isset($registerColumnWidths[$columnIndex])) {
                                return $cellMatch[0];
                            }
                            $attributes = preg_replace('/\s+width="[^"]*"/i', '', $cellMatch[1]);
                            return '<th width="' . $registerColumnWidths[$columnIndex++] . '%"' . $attributes . '>';
                        },
                        $rowMatch[2]
                    );
                    return '<tr' . $rowMatch[1] . '>' . $cells . '</tr>';
                },
                $headingMatch[1]
            );
            return '<thead class="register-heading">' . $heading . '</thead>';
        },
        $html,
        1
    );
    
    // TCPDF does not reliably use the colgroup widths. Put the four header
    // column widths directly on the live cells and remove the invalid colspan
    // from the fourth cell so the row remains a four-column grid.
    $html = preg_replace(
        '/<td class="details-label"([^>]*)>/i',
        '<td class="details-label" width="22%"$1 nowrap="nowrap">',
        $html
    );
    $html = preg_replace(
        '/<td class="details-value company-value"([^>]*)>/i',
        '<td class="details-value company-value" width="24%"$1 nowrap="nowrap">',
        $html,
        1
    );
    $html = preg_replace(
        '/<td class="principal-label"([^>]*)>/i',
        '<td class="principal-label" width="24%"$1 nowrap="nowrap">',
        $html
    );
    $html = preg_replace(
        '/<td class="company-value"\s+colspan="3"([^>]*)>/i',
        '<td class="company-value" width="30%"$1 nowrap="nowrap">',
        $html,
        1
    );

    $style = '<style type="text/css">'
        // A CSS-padded wrapper only affects the first fragment when TCPDF
        // splits a table across pages.  Continuation fragments are placed at
        // the PDF margin, which shifts the repeated heading and body to the
        // left.  Let the PDF margins provide the page inset on every page.
        . '.form-page {'
        . 'width: 100%; min-height: 0; margin: 0; padding: 0; overflow: visible;'
        . '}'
        . '.details-table { table-layout: fixed; font-size: 10px; }'
        . '.details-table .details-label { width: 22%; }'
        . '.details-table .details-value { width: 24%; }'
        . '.details-table .principal-label { width: 24%; }'
        . '.details-table .company-value { white-space: nowrap; }'
        . '</style>';

    if (stripos($html, '</head>') !== false) {
        return preg_replace('/<\/head>/i', $style . '</head>', $html, 1);
    }

    return $style . $html;
}

/**
 * Keep the Form XIII PDF header aligned with the compact Form XX header.
 */
function addFormXIIIPdfFormatting($html)
{
    $headerWidths = array(
        'left-header' => '41%',
        'center-header' => '18%',
        'right-header' => '41%'
    );
    foreach ($headerWidths as $className => $width) {
        $html = preg_replace(
            '/<td class="' . preg_quote($className, '/') . '"([^>]*)>/i',
            '<td class="' . $className . '" width="' . $width . '"$1>',
            $html,
            1
        );
    }

    // Put the contractor label and value on the same reliable TCPDF grid.
    $html = preg_replace(
        '/<td class="info-label"([^>]*)>/i',
        '<td class="info-label" width="55%"$1 nowrap="nowrap">',
        $html,
        1
    );
    $html = preg_replace(
        '/(<td class="info-label"[^>]*>.*?<\/td>\s*)<td([^>]*)>/is',
        '$1<td width="45%"$2 nowrap="nowrap">',
        $html,
        1
    );
    $html = preg_replace(
        '/<td class="address-line"([^>]*)>/i',
        '<td class="address-line"$1 nowrap="nowrap">',
        $html
    );
    $html = preg_replace(
        '/<div class="right-line"([^>]*)>/i',
        '<div class="right-line"$1 style="white-space: nowrap;">',
        $html
    );

    $style = '<style type="text/css">'
        . '.header-table .left-header { width: 41%; font-size: 8px; }'
        . '.header-table .center-header { width: 18%; }'
        . '.header-table .right-header {'
        . 'width: 41%; font-size: 7.5px; padding-top: 0;'
        . '}'
        . '.header-table .info-table td, .header-table .right-line {'
        . 'white-space: nowrap;'
        . '}'
        . '</style>';

    if (stripos($html, '</head>') !== false) {
        return preg_replace('/<\/head>/i', $style . '</head>', $html, 1);
    }

    return $style . $html;
}

/**
 * Form XXIII uses the same three-column header markup as Form XXII, so reuse
 * the established TCPDF-only formatting instead of duplicating its rules.
 */
function addFormXXIIIPdfFormatting($html)
{
    $html = addFormXXIIPdfFormatting($html);
    $style = '<style type="text/css">'
        . '.register-table tbody .data-row td,.register-table tbody .total-row td{'
        . 'padding:7px 3px;line-height:1.35;vertical-align:middle;}'
        . '.register-table td.amount{text-align:right;padding-right:6px;}'
        . '</style>';
    return preg_replace('/<\/head>/i', $style . '</head>', $html, 1);
}