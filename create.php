<?php
    // required headers
    header("Access-Control-Allow-Origin: *");
    header("Content-Type: application/json; charset=UTF-8");
    header("Access-Control-Allow-Methods: POST");
    header("Access-Control-Max-Age: 3600");
    header("Access-Control-Allow-Headers: Content-Type, Access-Control-Allow-Headers, Authorization, X-Requested-With");
    require_once __DIR__ . '/pdf/vendor/autoload.php';
    // get posted data
    if($_SERVER['REQUEST_METHOD'] =='POST') 
    {
        $url = $_SERVER['HTTP_HOST'];
       // $exp = explode('')
        if(isset($_FILES)) 
        {
            $mpdf = new \Mpdf\Mpdf(); 
            $html="";
            $allowed = array("png","jpg","jpeg","gif","bmp");
            $error=false;

            $name = "Name: ".$_POST["name"]."<br>";
            $email = "E-mail: ".$_POST["e-mail"]."<br>";
            $account = "Account: ".$_POST["account"]."<br>";
            $amount = "Amount: ".$_POST["amount"]."<br>";
            $description = "Description: ".$_POST["description"]."<br>";
            $html = $name.$email.$account.$amount.$description."<br>";

           foreach($_FILES["images"]["name"] as $key=>$tmp_name) {
               //var_dump("sds");
                $image_name = $_FILES['images']['name'][$key];
                $image_tmp = $_FILES['images']['tmp_name'][$key];
                $file_extension = pathinfo($image_name, PATHINFO_EXTENSION);
               
                if (! in_array($file_extension, $allowed)) {
                    $error=true;
                }

                if (strpos($file_extension, "pdf") !== false){
                    //  $targetfolder = $url."/api/pdf/files/";

                    // $targetfolder = $targetfolder . $image_name ;

                    // if(move_uploaded_file($image_tmp, $targetfolder))

                    // {
                        $rpdf = new \Gufy\PdfToHtml\Pdf('$image_tmp');

                        // convert to html string
                        $rhtml = $rpdf->html();
                        $html .= $rhtml;
                        $error=false;
                        //echo "The file ". basename( $_FILES['file']['name']). " is uploaded";

                    // }
                    //$html .= '<embed src="'.$image_tmp.'" type="application/pdf" width="100%" height="100%">';
                    
                    
                }

                $html .='<img src="'.$image_tmp.'" alt="">';
            }
            if($error==true) {
                 echo json_encode(array("message" => "Invalid file extension found. Only png,jpeg,jpg, pdf files are allowed to upload."));die;
            }
            try {
               
               $mpdf->WriteHTML($html);
               $filename="pdf-".time().".pdf";
               $res = $mpdf->Output('pdf/files/'.$filename, \Mpdf\Output\Destination::FILE);
                if($res==null) {
                    // set response code - 201 created
                    http_response_code(201);
                    // tell the user

                   mail($email,"Bilag uploaded",$description);

                    echo json_encode(array("message" => "PDF created successfully",'download'=>$url."/api/pdf/files/".$filename));die;
                } else {
                    // if unable to create the product, tell the user
                    // set response code - 503 service unavailable
                    http_response_code(503);
                    // tell the user
                    echo json_encode(array("message" => "Unable to create product."));die;
                }
            } catch(Exception $e) {
                 echo json_encode(array("message" => $e));die;
            }

        }  else  {
            // tell the user data is incomplete
            // set response code - 400 bad request
            http_response_code(400);
         
            // tell the user
            echo json_encode(array("message" => "Unable to create product. Data is incomplete."));die;
        }
    } else {
        echo json_encode(array("message" => "Only POST request is allowed"));die;
    }
?>