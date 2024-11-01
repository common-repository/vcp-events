<?php
//namespace vcpplugin;

header('Content-Type: application/xml;');
header('Content-Disposition: attachment; filename='.'vcp-src-db'.'.xml;');
$xml_path = dirname(__FILE__).'/vcp-src-db.xml';
readfile($xml_path);
?>