<?php

/**
 * Add export-only table spacing without changing the browser report templates.
 *
 * TCPDF honours cell padding and line-height more reliably than CSS min-height.
 * Letting the cells grow with their content also prevents wrapped names from
 * overlapping the following row.
 */
function addOtherReportPdfSpacing($html, $repeatTableHeaders = true, $cellPadding = '5px 3px')
{
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

    // TCPDF automatically repeats every THEAD when a table flows onto a new
    // page. Some registers, including Form XXIII, must continue as one list
    // without printing the column header again. Use a regular table body for
    // PDF rendering in that case; the browser and Excel HTML stay unchanged.
    if (!$repeatTableHeaders) {
        $html = preg_replace('/<thead\b([^>]*)>/i', '<tbody$1>', $html);
        $html = preg_replace('/<\/thead>/i', '</tbody>', $html);
    }

    if (stripos($html, '</head>') !== false) {
        return preg_replace('/<\/head>/i', $style . '</head>', $html, 1);
    }

    return $style . $html;
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
    $headingWidths = array(3, 18, 17, 8, 6, 6, 6, 6, 6, 6, 6, 6, 6); // array(3, 18, 16, 10, 8, 7, 8, 7, 6, 5, 8, 4);
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
                '<th width="4%"$1>',
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
        . '.header-table .left-header { width: 41%; font-size: 8px; }'
        . '.header-table .center-header { width: 18%; }'
        . '.header-table .right-header { width: 41%; font-size: 7.5px; }'
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