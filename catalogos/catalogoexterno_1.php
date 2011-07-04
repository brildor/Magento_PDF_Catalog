<?php
/**
 * Error reporting
 */
error_reporting(E_ALL | E_STRICT);

$mageFilename = '../app/Mage.php';
require_once $mageFilename;
ini_set('display_errors', 1);
umask(0);

try {
	Mage::app('default'); //para inicializar el main objeto: Mage::app(), or Mage::app('store_code'), etc.
	Mage::getSingleton('core/session', array('name' => 'frontend')); //selecciona las session de la parte publica
	$cliente = Mage::getSingleton('customer/session'); //selecciona la session del cliente devuelve boleano Mage::log( $session->getId() ); Mage::log( $_SESSION );
        
        //$_product = Mage::getModel('catalog/product')->load('5255');
        $_products  = Mage::getModel('catalog/product')
                            ->getCollection()
                            ->addAttributeToSelect('*')
                            ->addAttributeToFilter('visibility','4')
                            ->addAttributeToFilter('status','1')
                            ->load();

        //Zend_Debug::dump($_product->getCategoryIds());
        //Zend_Debug::dump($_product->getVisibility());
        foreach ($_products as $_product) {
            
            if(in_array('89', $_product->getCategoryIds()) && $_product->getEstadocustom()!="" ){

                $valores     = $_product->getAttributeText('estadocustom');
                $numero      = count($_product->getAttributeText('estadocustom') );

                if($numero==2){
                    //echo $valores[0];
                }elseif($numero==3){
                    //echo $valores[1];
                }

                //echo $_product->getSku().'<br>';
                

                $val1 = $_product->debug();
                echo 'disponibilidad';
                Zend_Debug::dump( $_product->getAttributeText('disponibilidad') );
                echo 'Estadocustom';
                Zend_Debug::dump( $_product->getAttributeText('estadocustom') );
                echo 'Tier prices';
                Zend_Debug::dump( $_product->getTierprice() );
                echo '<hr>';
            }
        }

        Zend_Debug::dump($val1);


} catch (Exception $e) {

        Zend_Debug::dump($e);

}
?>