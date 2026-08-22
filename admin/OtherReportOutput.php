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