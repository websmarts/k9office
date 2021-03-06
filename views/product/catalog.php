<!DOCTYPE HTML PUBLIC "-//W3C//DTD HTML 4.0 Transitional//EN">

<html>

    <head>
        <meta http-equiv="Content-Type" content="text/html;charset=utf-8" />
        <title>Print Catalog</title>

        <style media="print">

        .noprint {

            display:  none;

        }

        </style>



        <style>

            .noprint { display: inline}

            body {font-family: helvetica; font-size:8pt}

            .pageCellInner{

                padding: 30px 5px 0px 5px;

            }

            .pageCell {

                border-bottom: 0px solid red;



                width:675px;

                background: url(../img/printed_catalog_bg4.jpg) 0px 0 no-repeat;

                height:980px;

                overflow: visible;

                border: 0px solid red;



            }

            h2 {

                color: #00335B;

                font-size:14px;

                text-transform: uppercase;

                font-weight: normal;

                margin:0;

                position: relative;

                top: -20px;

            }

            /*h2 span {display: block; float: right;font-weight: normal;font-size: 13px; color:#555}*/

            h2 span {display:block; position: relative; left: 322px; font-family: courier; top: 927px; font-size:20px;color: #fff;font-weight: bold}

            h2 span.twodigits {left: 316px; }



            .category

            {

                font-weight: bold;

                font-size: 120%;

                clear: both;

            }

            .pgroupcell{border: 0px solid #000; overflow: hidden; width: 650px; margin-bottom: 10px;}

            .ptypecell{border-top: 1px dotted #333;  margin-bottom: 10px; ;padding-top: 5px;width:660px;overflow: hidden;clear:right}

            .pcell{ border-bottom: 1px solid #ccc; width:648px; overflow: hidden;}



            .typename{font-size: 12pt; color: #0a0647; font-weight: bold}

            .typename span {font-size:10pt; font-weight:normal}

            .typedesc{float: left; font-size: 10pt;min-height: 20px; font-style :italic;  width: 530px; background-color: transparent;margin-bottom: 10px;}



            .pimage {float: right; width:100px; min-height:105px; border:0px solid blue;overflow:hidden;clear: right;padding-right:2px;}

            .pimage img {width:100px;}

            .pcode{ float:left;  clear: left; width:80px;font-size:100%;margin-right: 4px;}

            .pdesc{ float:left;  width:220px}

            .pdescwide {float:left; width: 290px;/** .pdesc width + .psize width */}

            .psize{ float:left;  width:140px}

            .pbarcode{ float:right;  width:78px;margin-left:4px;}

            .pcolorcell {float:left; width:110px; overflow: hidden;padding-left:3px;}

            .pcolor { float: left;  border: 0px solid #ccc; width: 17px; height: 15px;margin-right:3px;}

            .pcode a{text-decoration: none; color: #000}

            .pcode a:hover {text-decoration: underline;}



            .bold { font-weight: bold;}



        </style>

    </head>







    <body >
<?php

if ($_GET['pager'] == 1) {

    echo '<a class="noprint" href="?pager=0">Turn off pager</a>';

} else {

    echo '<a class="noprint" href="?pager=1">Turn on pager</a>';

}

?>

    <form method="post" action="">







        <?php

ob_start();

//echo pr($data['data']);

$counter = 0;

// Output a page break before these types

global $index; // table of contents

global $pagenum;

$pageStatus = 'closed';

$pagenum = 0;

global $pageBreakMarkers;

$pageBreakMarkers = array();

if (is_array($data['pagebreakmarkers'])) {

    $pageBreakMarkers = $data['pagebreakmarkers'];

}

?>



        <?php

$started = 0;

global $cat;

global $subcatname;

foreach ($data['data']['categorys'][0] as $cat) {

    //pr($cat);

    // limit length of catalog - for testing basically

    ++$counter;

    if ($counter > 1) {

        //continue;

    }

    $subcatname = '';

    $products = $data['data']['products'][$cat->id];

    //echo '<div class="dummy"><!-- dummy start page cell --> '."\n";

    if (count($products) > 0) {
        output_category($cat->name, $products);
    }

    $subcats = $data['data']['categorys'][$cat->id];

    if (is_array($subcats) && count($subcats)) {

        foreach ($subcats as $subcat) {

            $products = $data['data']['products'][$subcat->id];

            if (count($products) > 0) {
                $subcatname = $subcat->name;
            }
            // global for page header use

            output_category($subcat->name, $products);

        }

    }

}

if ($_GET['pager'] > 0) {

    //echo '<div class="noprint" style="margin-left:300px;"> Last break on typeid= <input name="pagebreakmarker['.$pagenum.']" value="'.$pageBreakMarkers[$pagenum].'" type="text" size="4"  /><input type="submit" name="b" value="set" /></div>';

    pagebreak('last');

}

$content = ob_get_contents();

ob_end_clean();

$printIndexPage = true;

if ($printIndexPage) {

    //echo pr($index)  ;

    echo '<h1>K9Homes Pet Products<br />Product Catalog</h1>';

    echo '<div style="page-break-before:always">&nbsp;</div>';

    // output catalog index

    foreach ($index as $typename => $pages) {

        echo '<h4>' . $typename . ' : ' . implode(',', array_keys($pages)) . '</h4>';

        //echo '<p>'..'</p>';

        //echo pr(array_keys($pages));

        /*

    foreach($pages as $pagenum => $products){

    echo '<h4>Page '.$pagenum .' - '. $typename.'</h4>';

    echo '<ul>';

    foreach($products as $k =>$v){

    echo '<li>'.$v.'</li>';

    }

    echo '</ul>';

    }

     */

    }

    echo '<div style="page-break-before:always">&nbsp;</div>'; // start category with a page break

}

// output catalog pages

echo $content;

function output_category($category, $catProducts)
{

    global $pageNumber;

    static $categoryCounter;

    $categoryCounter++;

    //if($categoryCounter++ > 0)

    pagebreak('193');

    // echo '<div class="category">' . $category . '</div>';

    if (count($catProducts) > 0)

    //echo '<div class="pgroupcell">'."\n";

    {
        output_type_products($catProducts);
    }

    //echo '</div>'."\n";

}

function output_type_products($catProducts)
{

    global $pageBreakMarkers, $pagenum, $cat, $index;

    if (count($catProducts) > 0) {

        foreach ($catProducts as $typeid => $typeProducts) {

            $cnt = count($typeProducts);

            if (count($typeProducts) > 0) {

                // If typename =='xxx' then output a page breah

                /*

                if( in_array($typeProducts[0]->typeid,$pageBreakMarkers))

                pagebreak('marker');

                 */

                //echo '<div style="background: yellow" >CHECKING ' .$typeProducts[0]->typeid.' pagenum='.$pagenum.' | '.$breakMarkers[$pagenum].'</div>' ;

                //echo '<div style="background: orange" >Result = ' .pr($breakMarkers).'</div>' ;

                if ($pageBreakMarkers[$pagenum] == $typeProducts[0]->typeid) {

                    pagebreak('marker');

                }

                echo '<div class="ptypecell">' . "\n";

                $typename = strip_tags($typeProducts[0]->name);

                // Add to index

                $index[$cat->name][$pagenum][] = $typename;

                if ($_GET['pager'] == 1) {

                    $typename .= ' <span class="noprint">[' . $typeProducts[0]->typeid . ']</span>';

                }

                echo '<div class="typename">';

                if ($typeProducts[0]->aus_made) {

                    echo '<img src="http://www.k9homes.com.au/catalog/images/ausmade.jpg" /> ';

                }

                echo $typename . '</div>' . "\n";

                echo '<div class="typedesc">' . nl2br(strip_tags(stripslashes($typeProducts[0]->type_description))) . '</div>' . "\n";

                echo '<div class="pimage"><img src="http://www.k9homes.com.au/fido/public/source/tn/' . $typeProducts[0]->typeid . '.jpg" alt=""  /></div>' . "\n";

                // Header rows for product info

                echo '<div class="pcell bold">' . "\n";

                echo '<div class="pcode">Product Code</div>' . "\n";

                echo '<div class="pdesc">Description</div>' . "\n";

                echo '<div class="psize">Size</div>' . "\n";

                echo '<div class="pcolorcell ">&nbsp;</div>' . "\n";

                echo '<div class="pbarcode">Barcode</div>' . "\n";

                echo '</div>' . "\n";

                output_products($typeid, $typeProducts);

                echo '</div>' . "\n";

            }

        }

    }

}

function output_products($typeid, $products)
{

    $i = 0;

    foreach ($products as $p) {

        $i++;

        //$color = substr($p->product_code,strrpos($p->product_code,'-')+1);

        echo '<div class="pcell">' . "\n";

        echo '<div class="pcode"><a target="admin" href="http://www.k9homes.com.au/catalog/admin/?m=product&a=edit&product_code=' . $p->product_code . '">' . $p->product_code . '</a></div>' . "\n";

        // clean up desc to remove tags

        $desc = str_replace('<', ' <', $p->description); // put space in before <br>

        $desc = strip_tags($desc); // strip br & b out

        // merge desc and size if size is empty

        if (empty($p->size)) {

            echo '<div class="pdescwide">' . stripslashes($desc) . '</div>' . "\n";

        } else {

            echo '<div class="pdesc">' . stripslashes($desc) . '</div>' . "\n";

            echo '<div class="psize">' . stripslashes(strip_tags($p->size)) . '</div>' . "\n";

        }

        echo '<div class="pcolorcell">';

        if (strtolower($p->color_background_color) != 'ffffff') {

            echo '<div class="pcolor" style="background: #' . $p->color_background_color . ';"></div>';

        }

        echo $p->color_name . '</div>' . "\n";

        echo '<div class="pbarcode">' . $p->barcode . '</div>' . "\n";

        echo '</div>' . "\n";

        if ($typeid == 19) {

            if ($i > 40) {

                echo "<p>cont'd</p>";

                echo '</div>';

                pagebreak('productlength');

                echo '<div class="ptypecell">' . "\n";

                echo "<p>cont'd</p>";

                $i = 0;

            }

        }

    }

}

function pagebreak($type = 'cat')
{

    global $pagenum, $cat, $subcatname, $pageStatus, $pageBreakMarkers;

    if ($pageStatus == 'open') {

        echo "\n" . '</div></div><!-- end of pageCell ' . $pagenum . ' -->' . "\n";

        $pageStatus = 'closed';

        if ($_GET['pager'] == 1) {

            echo '<div class="noprint" style="margin-left: 300px;">' . $type . ' - break on typeid= <input name="pagebreakmarker[' . $pagenum . ']" value="' . $pageBreakMarkers[$pagenum] . '" type="text" size="4"  /><input type="submit" name="b" value="set_pagebreak_' . $pagenum . '" /></div>';

        }

    }

    $pagenum++;

    if ($pagenum > 1) {

        echo "\n" . '<div style="page-break-before:always"></div>' . "\n"; // start category with a page break

    }

    echo "\n" . '<a class="pageanchor" name="page' . $pagenum . '"></a>';

    echo "\n" . '<div class="pageCell"><!-- begin page cell ' . $pagenum . ' with background -->' . "\n";

    echo "\n" . '<div class="pageCellInner">' . "\n";

    $pageStatus = 'open';

    if (!empty($subcatname)) {

        $catname = $cat->name; //. ' <br> ' . $subcatname;

    } else {

        $catname = $cat->name;

    }

    if ($pagenum > 9) {

        $spanclass = ' class="twodigits" ';

    } else {

        $spanclass = '';

    }

    echo '<h2>' . $catname . '<span ' . $spanclass . '>' . $pagenum . '</span></h2>' . "\n";

}

?>

        </form>

    </body>

</html>