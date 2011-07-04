<?php
//muestra el tiempo de eject del script
$mtime = microtime(); $mtime = explode(" ",$mtime); $mtime = $mtime[1] + $mtime[0]; 
$starttime = $mtime;

require '../app/Mage.php'; //esto es necesario si es un fichero fuera de magento

try {
	Mage::app('default'); //para inicializar el main objeto: Mage::app(), or Mage::app('store_code'), etc.
	Mage::getSingleton('core/session', array('name' => 'frontend')); //selecciona las session de la parte publica
	$cliente 	= Mage::getSingleton('customer/session'); //selecciona la session del cliente devuelve boleano Mage::log( $session->getId() ); Mage::log( $_SESSION );
	$i 		= 0; $y = 0;
	$child 		= $_GET["cat_id"]; //id categoria

        if(!$child){

            $child = 89;
        }
	
	//Mage::log(  $cliente->getCustomerGroupId() , null, 'catalogexterno.log' );
	
	/**
		Muestra lass categorias 
	**/
	$collectionCat = Mage::getModel('catalog/category')->getCollection() 
					->addAttributeToSelect('name') 
					->addAttributeToSelect('level') 
					->addAttributeToSelect('is_active');
					
	$ArrayCats	   = array();
	$CatsAllow	   = array(89,143,3); 
	$confTier 	   = array();
	$confPrice	   = array();
	
	foreach($collectionCat as $col){
		if(in_array($col->getParent_id(), $CatsAllow)){
			if( $col->getIs_active() == 1 && $col->getLevel() >= 3){
				$ArrayCats[] =  (int)$col->getEntity_id(); 
			}
		}
	}
	
	
	/**
		Muestras los productos de 1 categoria
	**/	
	//if( $cliente->isLoggedIn() ){
		
	//if( in_array($child, $ArrayCats) ){
	
		$urlMedia 	= Mage::getBaseUrl(Mage_Core_Model_Store::URL_TYPE_MEDIA).'catalog/product'; //URL para las imagenes
		$category 	= Mage::getModel('catalog/category')->load(trim(89)); //Carga las categorias
		$contenido	= '<table width="600"><tr>';
				
		foreach ($category->getProductCollection() as $product) {
			
			$product 		= Mage::getModel('catalog/product')->load($product->getId()); //todos los datos del producto
			
			$productImage 	= Mage::helper('catalog/image')->init($product, 'image')->resize(120); //$productImage = $urlMedia.$product->getThumbnail();
			$tier_price 	= $product->getTierprice(); //obtiene una array
			$tipo_producto	= $product->getType_id(); //configurable, simple, group
			$tipo_catalogo	= $product->getTipo_catalogo(); //si tiene valor es visible 
			$disponibilidad	= $product->getAttributeText("disponibilidad"); //muestra la dispo
			$estadoCustom	= $product->getAttributeText("estadocustom"); //muestra la dispo
			$j 				= 0;//contador adicional
			
			//Debug
			//if($i==0){
				//Mage::log(  $estadoCustom , null, 'catalogexterno.log' );
			//}

			//if( $product->getVisibility() != 1 ){	
			if( $tipo_catalogo !='' && $disponibilidad!='Descatalogado' ){
						
				$modelConfigurable = Mage::getModel('catalog/product_type_configurable')->setProduct($product);
				$col 			   = $modelConfigurable->getUsedProductCollection()
										->addAttributeToSelect('*')
										->addFilterByRequiredOptions();
														
				if ($i%4==0 && $i!=0){
                                    if($i%3==0){$tercer = "tercer"; }else{ $tercer = $i; }
					$contenido	.= '</tr><tr class="linea-'.$tercer.'" >';
				}
				
				$contenido	.= 	'<td>'.
							'<img src="'.$productImage.'" />'.
							'<div class="comment-content clear-block prose"><h3>'.$product->getName().'</h3></div>'.
							'<div>'.$product->getSku().'</div>';
				
							if($disponibilidad){
								$contenido	.= '<br>Disponibilidad: <em>'.$disponibilidad.'</em><br>';
							}
							
							if($estadoCustom){
								foreach($estadoCustom as $estado){
									if($estado!="Selec. siempre"){
										$contenido	.= '<br /><em>'.$estado.'</em><br />';
									}
								}
							}
							
							if( $product->getDescuetopdf() != '' && $product->getDescuetopdf() > 0 ){ //Tier prices normales
								$contenido	.= '<br /> Blanco: <br />';
							}
							
				if($tipo_producto!="configurable"){
					$contenido	.=	'<div class="editable"> &euro; <b>'.number_format($product->getFinalPrice(), 2).'</b></div>';
				}
				
				//Tier prices normales
				foreach($tier_price as $tier ){
					$contenido	.= '<div class="editable">Compra '.number_format($tier['price_qty'], 0).' por &euro; <b>'.number_format($tier['price'], 2).'</b></div>';
				}//fin bucle tier prices
				
				//Tier prices camisetas con color
				if( $product->getDescuetopdf() != '' && $product->getDescuetopdf() > 0 ){
					$contenido	.= 	'<br /> Color: <br />'.	
									'<div class="editable">&euro; <b>'.number_format((($product->getFinalPrice()*$product->getDescuetopdf())/100)+$product->getFinalPrice(), 2).'</b></div>';
					foreach($tier_price as $tier ){
						$contenido	.= '<div class="editable">Compra '.number_format($tier['price_qty'], 0).' por &euro; <b>'.number_format((($tier['price']*$product->getDescuetopdf())/100)+$tier['price'], 2).'</b></div>';
					}//fin bucle tier prices
				}
				
				//Productos configurables
				if($tipo_producto=="configurable"){
					foreach($col as $simple_product){
						
						if($j==0 ){
							$confPrice1 = number_format($simple_product->getFinalPrice(), 2);
							$contenido	.= $confPrice1.'<br />';
							$f = 0;
							foreach($simple_product->getTierprice() as $tier ){
								if($f<=2){
									$confTier1   = number_format($tier['price'], 2);
									$contenido	.= '<div class="editable">Compra '.number_format($tier['price_qty'], 0).' por &euro; <b>'.number_format($tier['price'], 2).'</b></div>';
								}
								$f++;
							}
						}
						
						if( $confPrice1!=number_format($simple_product->getFinalPrice(), 2) ){
						
							if($confPrice1>number_format($simple_product->getFinalPrice(), 2)){$confContenido = "<br>Blanco ";}else{$confContenido = "<br>Color ";}

							$confContenido	.= 'precio: &euro; '.number_format($simple_product->getFinalPrice(), 2).'<br />';
							
							$f = 0;
							foreach($simple_product->getTierprice() as $tier ){
								if($f<=2){
									$confContenido	.= '<div class="editable">Compra '.number_format($tier['price_qty'], 0).' por &euro; <b>'.number_format($tier['price'], 2).'</b></div>';
								}
								$f++;
							}
						}
						
						$j++;
					}
					$contenido	.= $confContenido;
				}//fin de configurable
				
				$contenido	.=	'</td>';

                                $i++;		
				
			}//fin de tipo catalogo
			
		}//end foreach
			
//	}//fin de solo estas categorias
	
//}else{
	//$contenido = Zend_Debug::dump($child);
//}//fin de si esta logeado

} catch (Exception $e) {
    Mage::printException($e);
}

/*
 * muestra el tiempo de eject del script
 */
$mtime = microtime(); $mtime = explode(" ",$mtime); $mtime = $mtime[1] + $mtime[0]; 
$endtime = $mtime; $totaltime = ($endtime - $starttime); 
$tiempofinal = "<br /><ul><li>Tiempo en ejecucion catalogoexterno: <b>".$totaltime."</b> segundos</li>";

/* 
 * Verifica si estan cacheados las variables
 */
if (apc_fetch('colectionproductos')) {
 	// var_dump( $category->debug() );
} else {
  	$tiempofinal .= '<li>Sin cache en APC</li>';
}

/*
 * Verifica si el cliente esta logeado o no
 * */
if( $cliente->isLoggedIn() ){
	$tiempofinal .= '<li>El cliente <b>si</b> esta logeado</li>';
}else{
	$tiempofinal .= '<li>El cliente <b>no</b> esta logeado</li>';
}


/*
 * muestra el resultado final
 * */
if($contenido==1){
	echo 1; //si no esta logeado
}elseif($contenido==""){
	echo 0; //si no esta tipo_catalogo
}else{
	echo $contenido.'</tr></table>';
        //echo $tiempofinal;
}
?>