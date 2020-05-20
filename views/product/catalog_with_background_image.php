<!DOCTYPE HTML PUBLIC "-//W3C//DTD HTML 4.0 Transitional//EN">
<html>
    <head>
        <title>xCatalog</title>

        <style>

            body {font-family: helvetica; font-size:9pt}
            .pageCell {
                border-bottom: 0px solid red;
                padding: 100px 30px 1px 10px;
                width:1022px;
                background: url(../../img/printed_catalog_bg.jpg) -3px 0 no-repeat;
                min-height:1445px;
                overflow: hidden;

            }
            h2 {width: 902px; color: #666; text-transform: uppercase;font-weight: normal}
            /*h2 span {display: block; float: right;font-weight: normal;font-size: 13px; color:#555}*/
            h2 span {display:block; position: relative; left: 505px; top: 1375px; font-size:20px;color: #fff}
            .category
            {
                font-weight: bold;
                font-size: 120%;
                clear: both;
            }
            .pgroupcell{border: 0px solid #000; overflow: hidden; width: 902px; margin-bottom: 10px;}
            .ptypecell{border-top: 1px dotted #333; width: 900px; margin-bottom: 10px; ;padding-top: 5px;overflow: hidden}
            .pcell{ border-bottom: 1px solid #ccc; width:730px; overflow: hidden;}

            .typename{font-size: 12pt; color: #0a0647; font-weight: bold}
            .typedesc{font-size: 10pt;min-height: 20px; font-style :italic;  width: 700px; background-color: transparent;margin-bottom: 10px;}

            .pimage {float: right; width:155px; min-height:155px; border:0px solid blue;}
            .pcode{ float:left;  clear: left; width:110px}
            .pdesc{ float:left;  width:190px}
            .pdescwide {float:left; width: 330px;/** .pdesc width + .psize width */}
            .psize{ float:left;  width:140px}
            .pbarcode{ float:left;  width:100px}
            .pcolorcell {float:left; width:180px; overflow: hidden;}
            .pcolor { float: left;  border: 1px solid #ddd; width: 20px; height: 20px;margin-right:3px;}
            .pcode a{text-decoration: none; color: #000}
            .pcode a:hover {text-decoration: underline;}

            .bold { font-weight: bold;}
        </style>
    </head>



    <body >
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
//pr($data['pagebreakmarkers']);
$pageBreakMarkers = array();
if (is_array($data['pagebreakmarkers'])) {
    $pageBreakMarkers = $data['pagebreakmarkers'];
}

//$pageBreakMarkers = array(431,535,549,441,457,520,138,372,510,84,148,17,491,467,229,473,564,284,477,503,47,344,434,559);
/*
$pageBreakMarkers = array(  534, // Baskets
175, // Cushions
562, // igloos
116, // Mats
137, // Bowls
522, // Cat scratch items
138,408, // Cat
-1, // Dog Leather
84,146, // Dog Webbed
61, // Dog Nylon Webbed
500, // litter handling
15,17,531, // Dog Coats
466, // Dog skivvies
214,203,449, // Cat toys
490,364,238,284, // Dog toys
35,434,550, // Treats and Munchies
559 // xmas
);
 */

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
if ($_GET['debug'] == 1) {
    echo 'break on typeid= <input name="pagebreakmarker[' . $pagenum . ']" value="' . $pageBreakMarkers[$pagenum] . '" type="text" size="4"  /><input type="submit" name="b" value="set" />';
}
$content = ob_get_contents();
ob_end_clean();

$printIndexPage = false;
if ($printIndexPage) {
    //echo pr($index)  ;
    echo '<h1>K9Homes Pet Products<br />Product Catalog</h1>';
    echo '<div style="page-break-before:always">&nbsp;</div>';
    // output catalog index
    foreach ($index as $typename => $pages) {

        foreach ($pages as $pagenum => $products) {
            echo '<h4>Page ' . $pagenum . ' - ' . $typename . '</h4>';
            echo '<ul>';
            foreach ($products as $k => $v) {
                echo '<li>' . $v . '</li>';
            }
            echo '</ul>';
        }
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
    pagebreak();

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
            if (count($typeProducts) > 0)
            // If typename =='xxx' then output a page breah
            {
                if (in_array($typeProducts[0]->typeid, $pageBreakMarkers)) {
                    pagebreak();
                }
            }

            echo '<div class="ptypecell">' . "\n";
            $typename = $typeProducts[0]->name;

            // Add to index
            $index[$cat->name][$pagenum][] = $typename;
            if ($_GET['debug'] == 1) {
                $typename .= ' (' . $typeProducts[0]->typeid . ')';
            }
            echo '<div class="typename">' . strip_tags($typename) . '</div>' . "\n";
            echo '<div class="typedesc">' . nl2br(strip_tags(stripslashes($typeProducts[0]->type_description))) . '</div>' . "\n";
            echo '<div class="pimage"><img src="http://www2.k9homes.com.au/catalog/source/tn_' . $typeProducts[0]->typeid . '.jpg" alt="" width="150" /></div>' . "\n";
            // Header rows for product info
            echo '<div class="pcell bold">' . "\n";

            echo '<div class="pcode">Product Code</div>' . "\n";
            echo '<div class="pdesc">Description</div>' . "\n";
            echo '<div class="psize">Size</div>' . "\n";
            echo '<div class="pcolorcell ">Color</div>' . "\n";
            echo '<div class="pbarcode">barcode</div>' . "\n";

            echo '</div>' . "\n";

            output_products($typeid, $typeProducts);
            echo '</div>' . "\n";
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

        echo '<div class="pcode"><a target="admin" href="http://www2.k9homes.com.au/catalog/admin/?m=product&a=edit&product_code=' . $p->product_code . '">' . $p->product_code . '</a></div>' . "\n";

        // clean up desc to remove tags
        $desc = str_replace('<', ' <', $p->description); // put space in before <br>
        $desc = strip_tags($desc); // strip br & b out

        // merge desc and size if size is empty
        if (empty($p->size)) {
            echo '<div class="pdescwide">&nbsp;' . stripslashes($desc) . '</div>' . "\n";
        } else {
            echo '<div class="pdesc">' . stripslashes($desc) . '</div>' . "\n";
            echo '<div class="psize">' . stripslashes(strip_tags($p->size)) . '</div>' . "\n";
        }
        echo '<div class="pcolorcell"><div class="pcolor" style="background: #' . $p->color_background_color . ';"></div>' . $p->color_name . '</div>' . "\n";
        echo '<div class="pbarcode">' . $p->barcode . '</div>' . "\n";

        echo '</div>' . "\n";
        if ($typeid == 19) {
            if ($i > 48) {
                echo "<p>cont'd</p>";
                echo '</div>';
                pagebreak();
                echo '<div class="ptypecell">' . "\n";
                echo "<p>cont'd</p>";
                $i = 0;
            }
        }
    }
}

function pagebreak()
{
    global $pagenum, $cat, $subcatname, $pageStatus, $pageBreakMarkers;

    if ($pageStatus == 'open') {
        echo "\n" . '</div><!-- end of pageCell ' . $pagenum . ' -->' . "\n";
        $pageStaus = 'closed';

        if ($_GET['debug'] == 1) {
            echo 'break on typeid= <input name="pagebreakmarker[' . $pagenum . ']" value="' . $pageBreakMarkers[$pagenum] . '" type="text" size="4"  /><input type="submit" name="b" value="set" />';
        }
    }

    $pagenum++;

    echo "\n" . '<div style="page-break-before:always">&nbsp;</div>' . "\n"; // start category with a page break
    echo "\n" . '<div class="pageCell"><!-- begin page cell ' . $pagenum . ' with background -->' . "\n";
    $pageStatus = 'open';
    if (!empty($subcatname)) {
        $catname = $cat->name; //. ' > ' . $subcatname;
    } else {
        $catname = $cat->name;
    }
    echo '<h2>' . $catname . '<span>' . $pagenum . '</span></h2>' . "\n";

}

?>
        </form>
    </body>
</html>