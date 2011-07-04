<?php
/*
 * Programacion Externa para extraer productos de magento
 */
/**
 * Error reporting
 */
error_reporting(E_ALL | E_STRICT);


//muestra el tiempo de eject del script
//$mtime = microtime(); $mtime = explode(" ",$mtime); $mtime = $mtime[1] + $mtime[0]; $starttime = $mtime;


$mageFilename = '../app/Mage.php';

require_once '../fpdf16/fpdf.php';
require_once $mageFilename;

ini_set('display_errors', 1);
umask(0);

define('EURO', chr(128));
define('ACENTO', chr(195));

try {
    Mage::app('default'); //para inicializar el main objeto: Mage::app(), or Mage::app('store_code'), etc.
    
    class PDF extends FPDF {

        var $col = 0;

        function FPDF(){
            $margin = 18.15 / $this->k;
            $this->SetMargins( $margin, $margin, $margin );
        }

        function SetCol($col)
        {
            //Move position to a column
            $this->col=$col;
            $x=5+$col*55;
            $this->SetLeftMargin($x);
            $this->SetX($x);
        }

        function AcceptPageBreak()
        {
            if($this->col<3)
            {
                //Go to next column
                $this->SetCol($this->col+1);
                $this->SetY(10);
                return false;
            }
            else
            {
                //Regrese a la primera columna y emita un salto de página
                $this->SetCol(0);
                return true;
            }
        }


        //Cabecera de página
        /*function Header()
        {
            //Logo
            //$this->Image('logo_pb.png',10,8,33);
            //Arial bold 15
            $this->SetFont('Arial','B',15);
            //Movernos a la derecha
            $this->Cell(80);
            //Título
            $this->Cell(30,10,'Title',1,0,'C');
            //Salto de línea
            $this->Ln(20);
        }*/

        //Pie de página
        function Footer()
        {
            //Posición: a 1,5 cm del final
            $this->SetY(-15);
            //Arial italic 8
            $this->SetFont('Arial','I',8);
            //Número de página
            $this->Cell(0,10,'Pagina '.$this->PageNo().'/{nb}',0,0,'C');
        }
    }

    //Creación del objeto de la clase heredada
    $pdf = new PDF();
    $pdf->AliasNbPages();
    $pdf->AddPage();
    $pdf->SetFont('Arial','',7);


    //magento
    $category 	= Mage::getModel('catalog/category')->load(trim(89)); //Carga las categorias
    $urlMedia 	= Mage::getBaseUrl(Mage_Core_Model_Store::URL_TYPE_MEDIA).'catalog/product'; //URL para las imagenes
    $i = 0;
    $w = array(40,35,40,45);


    foreach ($category->getProductCollection() as $_product) {

        $product            = Mage::getModel('catalog/product')->load($_product->getId()); //todos los datos del producto
        $dispo              = $product->getAttributeText('disponibilidad');

        if(!is_array($dispo)){
            $dispo = array($dispo);
        }

        if($dispo[0]=="Habitualmente 5 a 7 días hábiles, dependiendo de talla y/o color"){
            $dispo[0] = 'De 5 a 7 días hábiles, dependiendo de talla/color';
        }

        if($product->getVisibility()=='4' && $product->getStatus()=="1" && !in_array( 'Descatalogado', $dispo )){

            //$productImage 	= Mage::helper('catalog/image')->init($product, 'image')->resize(100); //$productImage = $urlMedia.$product->getThumbnail();
            $productImage 	= Mage::helper('catalog/image')->init($product, 'image')->setQuality(100)->resize(100); //$productImage = $urlMedia.$product->getThumbnail();
            $productSku         = $product->getSku();
            $productName        = $product->getName();
            $colorCamiseta      = "";
            $price              = number_format($product->getFinalPrice(), 2,',','.').EURO;
            if($product->getDescuetopdf()){
                $colorCamiseta = ' | Color:'.number_format((($product->getFinalPrice()*$product->getDescuetopdf())/100)+$product->getFinalPrice(), 2,',','.').EURO;
                $price         = 'Blanco:'.$price;

            }
            
            
            $pdf->Image("$productImage");
            $pdf->SetFont('arial','B',7.3);
            $pdf->MultiCell( 46,3, utf8_decode($productName), 0, 2 );
            $pdf->SetFont('arial','',5);
            $pdf->Cell( 50,2, $productSku, 0, 2 );
            $pdf->SetFont('arial','B',7.5);
            $pdf->Cell( 50,4, $price.$colorCamiseta , 0, 2 );
            $pdf->SetFont('arial','',6);

            if($product->getTierprice()){ 
                $tiers = $product->getTierprice();
                

                $pdf->Cell( 50,2, utf8_decode($dispo[0]), 0, 2 );
                foreach ($tiers as $tierprice ){
                    $pdf->Cell( 50,3, number_format($tierprice['price_qty'], 0).' X '.number_format($tierprice['price'], 2, ',','.').' '.EURO.' /ud.', 0, 2 );
                }
                $pdf->Cell( 50,8, ' ', 0, 2 );
            }else{
                $pdf->Cell( 50,3, utf8_decode($dispo[0]), 0, 2 );
                $pdf->Cell( 50,10, ' ', 0, 2 );
            }

            $i++;
            if($i%4==0){
                $pdf->Ln(60);
            }
            //$pdf->Ln(50);
        }
    }
    $pdf->Output();



} catch (Exception $e) {

        Zend_Debug::dump($e);

}
