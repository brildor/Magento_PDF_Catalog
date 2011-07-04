<?php
//muestra el tiempo de eject del script
$mtime = microtime(); 
$mtime = explode(" ",$mtime); 
$mtime = $mtime[1] + $mtime[0]; 
$starttime = $mtime;
?>
<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Strict//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-strict.dtd">
<html xmlns="http://www.w3.org/1999/xhtml" xml:lang="es" lang="es">
<head>

	<title>Catalogos -  Tienda Brildor</title>
	<meta content="text/html; charset=utf-8" http-equiv="Content-Type" />
	<meta content="text/html; charset=utf-8" http-equiv="Content-Type" />

	<link type="text/css" rel="stylesheet" media="print" href="css/reset.css" />
	<link type="text/css" rel="stylesheet" media="print" href="css/base.css" />
	<link type="text/css" rel="stylesheet" media="print" href="css/print.css" />
	<link type="text/css" rel="stylesheet" media="print" href="css/print-custom.css" />

	<link type="text/css" rel="stylesheet" media="all" href="css/reset.css" />
	<link type="text/css" rel="stylesheet" media="all" href="css/base.css" />
	<link type="text/css" rel="stylesheet" media="all" href="css/print.css" />
	<link type="text/css" rel="stylesheet" media="all" href="css/print-custom.css" />

	<script type="text/javascript" src="http://ajax.googleapis.com/ajax/libs/jquery/1.3.2/jquery.min.js"></script>
	<!-- <script type="text/javascript" src="js/jquery-ui-1.7.1.custom.min.js"></script> -->
	<script type="text/javascript" src="js/jquery.custom.js"></script>

</head>
<body class="print rubik admin-static page-node-242">
<div class="limiter clear-block">
<div class="clear-block" id="content">
<!--<div class="print-header">
      <h1 class="site-name editable">Puedes copiar este contenido en tu procesador de textos u hoja de calculo.</h1>
</div>-->
<?php
require '../app/Mage.php'; //esto es necesario si es un fichero fuera de magento

try {
	Mage::app('default'); //para inicializar el main objeto: Mage::app(), or Mage::app('store_code'), etc.
	Mage::getSingleton('core/session', array('name' => 'frontend')); //selecciona las session de la parte publica
	$cliente 	= Mage::getSingleton('customer/session'); //selecciona la session del cliente devuelve boleano Mage::log( $session->getId() ); Mage::log( $_SESSION );
	
	//Mage::log(  $cliente->getCustomerGroupId() , null, 'catalogexterno.log' );
	
	(int)$idCatGet = $_GET["cat_id"];
	
	//Cacheo de APC
		//apc_add('URL media', $urlMedia, 120);
		//apc_add('MAGE APP catalogo', Mage::app(), 120);
		//apc_add('colectionproductos', $category->getProductCollection(), 120);
		
	/**
		Lista todas las categorias
	**/
	if( $cliente->getCustomerGroupId()== 4 ){ //4 es el grupo operario
		$collectionCat = Mage::getModel('catalog/category')->getCollection() 
						->addAttributeToSelect('name') 
						->addAttributeToSelect('level') 
						->addAttributeToSelect('is_active');
						
		$ArrayCats	   = array(); 
		$CatsAllow	   = array(89,143,3);
		
		echo '<div class="lista">Seleccione: <select>';
		foreach($collectionCat as $col){
				
			$nombreCat 	= (string)$col->getName();
			$idCat 		= (string)$col->getEntity_id();
			
			if(in_array($col->getParent_id(), $CatsAllow)){
			
				if( $col->getIs_active() == 1 && $col->getLevel() >= 3){
					//Mage::log( $col->debug() , null, 'catalogexterno.log' );
					//Mage::log( $col->getName() , null, 'catalogexterno.log' );
					//Mage::log( $col->getEntity_id() , null, 'catalogexterno.log' );
					
					$ArrayCats[] =  (int)$col->getEntity_id(); 
					echo '<option value="'.$idCat.'">'.$nombreCat.'</option>';
				
				}
			}
		}//enf foreach
		echo '</select></div>';
	}
	
} catch (Exception $e) {


    Zend_Debug::dump($e);
	
}
?>
<div id="cargando" class="clear-block" > <img alt="Cargando.." src="img/ajax-loader.gif" /> <br /> <span>Cargando....</span></div>
<div id="main" class="clear-block" ></div>
<div class="clear-block" id="footer">&copy; 2011 Brildor S.L.</div>
</div>
</div>

<?php
//muestra el tiempo de eject del script
$mtime = microtime(); $mtime = explode(" ",$mtime); $mtime = $mtime[1] + $mtime[0]; 
$endtime = $mtime; $totaltime = ($endtime - $starttime); 
$tiempofinal = "Tiempo en ejecucion de index: ".$totaltime." segundos"; 

// try accessing a stored value
if (apc_fetch('colectionproductos')) {
  echo "<br />";
 // var_dump( $category->debug() );
} else {
  $tiempofinal .= ' y Nada en la APC'; 
}

?>
    <script type="text/javascript" >mostrarProctos(<?php if($idCatGet){ echo $idCatGet; }else{ echo 91;} ?>);</script>

</body>
</html>