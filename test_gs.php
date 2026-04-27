<?php
$pdfcontent = "%PDF-1.4\n1 0 obj\n<< /Type /Catalog /Pages 2 0 R >>\nendobj\n2 0 obj\n<< /Type /Pages /Kids [3 0 R] /Count 1 >>\nendobj\n3 0 obj\n<< /Type /Page /Parent 2 0 R /MediaBox [0 0 612 792] /Resources << /Font << /F1 4 0 R >> >> /Contents 5 0 R >>\nendobj\n4 0 obj\n<< /Type /Font /Subtype /Type1 /BaseFont /Helvetica >>\nendobj\n5 0 obj\n<< /Length 44 >>\nstream\nBT\n/F1 24 Tf\n100 700 Td\n(Hello World) Tj\nET\nendstream\nendobj\nxref\n0 6\n0000000000 65535 f \n0000000009 00000 n \n0000000058 00000 n \n0000000115 00000 n \n0000000223 00000 n \n0000000311 00000 n \ntrailer\n<< /Size 6 /Root 1 0 R >>\nstartxref\n405\n%%EOF";

$pdf_path = sys_get_temp_dir() . '/test.pdf';
file_put_contents($pdf_path, $pdfcontent);

$jpg_path = $pdf_path . '.jpg';
$cmd = sprintf(
    "gs -q -dSAFER -dBATCH -dNOPAUSE -sDEVICE=jpeg -r150 -dFirstPage=1 -dLastPage=1 -sOutputFile=%s %s",
    escapeshellarg($jpg_path),
    escapeshellarg($pdf_path)
);
exec($cmd, $output, $return_var);
if ($return_var === 0 && file_exists($jpg_path)) {
    echo "Success: JPEG size " . filesize($jpg_path) . "\n";
} else {
    echo "Failed. Code $return_var\n";
}
