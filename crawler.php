<?php
$artigosPorPagina = 30;
$itemArray = 0;
for($pagina=0;$pagina<=120;$pagina+=$artigosPorPagina){

  $url = "https://www.gov.br/compras/pt-br/acesso-a-informacao/noticias?b_start:int=$pagina";
  $htmlInput = file_get_contents($url);
  
  $doc = new DOMDocument();
  @$doc->loadHTML($htmlInput);
  $xPath = new DOMXpath($doc);
  $artigos = $xPath->query("//article[contains(@class, 'tileItem')]");
  
  foreach($artigos as $linha) {

    $itemArtigo[$itemArray] = array();
    $tagAncora = $linha->getElementsByTagName("a");
    foreach($tagAncora as $item) {

      if($item->parentNode->tagName == "h2") {

        $detalhe =  $item->getAttribute("href");
        $manchete = trim(preg_replace("/[\r\n]+/", " ", $item->nodeValue));
        $itemArtigo[$itemArray] += ['detalhe' => $detalhe,'manchete' => $manchete];

      }

    }
    
    $tagIcone = $linha->getElementsByTagName("i");
    foreach($tagIcone as $item){
    
      $class = $item->getAttribute("class");
      if($class=="icon-day"){
        $itemArtigo[$itemArray] += ['data' => $item->parentNode->nodeValue];
      }
      if($class=="icon-hour"){
        $itemArtigo[$itemArray] += ['hora' => $item->parentNode->nodeValue];
      }

    }
    
    $itemArray++;

  }
  
}
$file = "artigos.csv";
$escrever = fopen($file,"a+");
if(filesize($file)==0){

  $cabecalho =  utf8_decode("Link;Manchete;Data;Hora\n");
  fwrite( $escrever, $cabecalho );

}
foreach($itemArtigo as $item) {

   $verificarNoArquivo = file_get_contents($file);
   $textoBuscar = trim($item['data']).";".trim($item['hora']);
   if(!strpos($verificarNoArquivo, $textoBuscar) !== FALSE){
   
     $manchete = str_replace(";","",trim($item['manchete']));
     $string =  utf8_decode(trim($item['detalhe']).";".$manchete.";".trim($item['data']).";".trim($item['hora']))."\n";
     fwrite( $escrever, $string );

   }

}
fclose( $escrever );
?>
<script>location.href="<?=$file?>"</script>
