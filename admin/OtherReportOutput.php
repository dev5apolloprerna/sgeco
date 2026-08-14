<?php

/**
 * Add export-only table spacing without changing the browser report templates.
 *
 * TCPDF honours cell padding and line-height more reliably than CSS min-height.
 * Letting the cells grow with their content also prevents wrapped names from
 * overlapping the following row.
 */
function addOtherReportPdfSpacing($html)
{
    $style = '<style type="text/css">'
        . '.register-table tbody.register-body td,'
        . '.register-table tbody .data-row td {'
        . 'padding: 5px 3px; line-height: 1.25; vertical-align: middle;'
        . '}'
        . '</style>';

    if (stripos($html, '</head>') !== false) {
        return preg_replace('/<\/head>/i', $style . '</head>', $html, 1);
    }

    return $style . $html;
}