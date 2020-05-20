<?php include(getcwd().'/fpdf16/fpdf.php')?>
<?php
class PDF extends FPDF
{
//Load data

 function productCell($img)
{
    //Header
    
    $this->Cell(40,9,$img,1);
    $this->Ln();
    
}

}

$pdf=new PDF();
//Column titles

$pdf->SetFont('Arial','',9);
$pdf->AddPage();
$pdf->productCell('tjis is an image');
$pdf->productCell('tjis is also an image'); 
$pdf->Output();


exit;
?>

<?php 
$pageNumber=0; 

// put a page break after product count
$productCountForPageBreaks = array(4,8,12,16,22,28,33);


foreach($productCountForPageBreaks as $v){
    $pagebreaks[$v]  =1;
}
$productCounter= 0;




?>




<?php //echo pr($data['data']['products'])?>

 <?php foreach ($data['data']['categorys'][0] as $cat){
      //pr($cat);
      
      $products = $data['data']['products'][$cat->id];
      output_category($cat->name,$products,$pagebreaks,$pageNumber,$productCounter);
      
      $subcats =  $data['data']['categorys'][$cat->id];
      if(is_array($subcats) && count($subcats)){
          foreach($subcats as $subcat) {
          
            $products = $data['data']['products'][$subcat->id];
            output_category($subcat->name,$products,$pagebreaks,$pageNumber,$productCounter);  
          }
      }
      
 }






function output_category ($category,$products,$pagebreaks,$pageNumber,&$productCounter){
   
     if (!count($products)){
     return;
     }
     
     ?>
    

 
<div style="font-size:120%;font-weight: bold;color: #888"><?=$category?></div>
<?php foreach($products as $typeid => $ps):?>

     <?php
     if(!count($ps)){
     continue;
     }
     ?>
   
     
     <?php 
        if($pagebreaks[$productCounter] ){
            echo '<div style="page-break-before:always">Page:'.++$pageNumber.'</div>';
            
        } 
    ?>
      
     <?php ++$productCounter; ?> 
     
     <?php if ($productCounter > 30)
        exit;
        ?>
        
        
 <?php $imagedone=0;?>
 <div style="width:700px;border:1px solid #000; margin-bottom:5px;padding:5px;overflow: auto" >
 <?php foreach ($ps as $p):?>
 
<?php
if(!$imagedone) {
        
    echo '<div style="float:left;width:150px;margin-right:10px"><img src="/source/tn_'.$p->typeid.'.jpg" alt="" width="150" /></div>';
    $imagedone=1;
}

?> 
 
   
 
<div style="float:right;margin-left:20px;width:500px;overflow:hidden" >
    <?php if($imagedone++ == 1):?>
    <div style="font-weight:bold;font-size:12pt"><?=$productCounter?> <?=$p->name?></div>
    <?php endif;?>
    
    <div style="float:left;width:140px; margin-right:10px"><?=$p->product_code?></div>
    <div style="float:left;width:200px;margin-right:10px"><?=$p->description?></div>

</div>
<?php endforeach;?>
</div> 



<br clear="left"  />

<?php endforeach;?>

<?php } ?>
</body>
</html>
