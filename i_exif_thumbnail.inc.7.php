<?php
// http://192.168.13.250/yaya/pwg/_data/i/galleries/201o/2015/2015-01-06/20150106T104030_dscn3803-th.jpg
// header('Content-type: text/html');
// echo $_SERVER['QUERY_STRING'];
// echo preg_match('/^\/galleries\/(.*)\-(sq|th)\.(jpg|JPG)$/', $_SERVER['QUERY_STRING'], $matches);
// echo preg_match('/^\/galleries\/(.*)\-(sq|th)_(jpg|JPG)$/', key($_GET), $matches);

$canonCase = false ;

if(1 && (
  preg_match('/^\/galleries\/(.*)\-(sq|th)\.(jpg|JPG)$/', $_SERVER['QUERY_STRING'], $matches) ||
  preg_match('/^\/galleries\/(.*)\-(sq|th)_(jpg|JPG)$/', key($_GET), $matches)
)) {
	$image_source_filename = /*utf8_decode*/(urldecode('galleries/'.$matches[1].'.'.$matches[3]));
	if (file_exists($image_source_filename) || (($image_source_filename=str_replace('_', ' ', $image_source_filename)) && file_exists($image_source_filename)))
	{
// 		$fichier_date = $image_source_filename ;
// debut:
		$thumb_data = @exif_thumbnail($image_source_filename, $width, $height, $type) ;
		if ($thumb_data==false) {
            $return_var=null;
ob_start();
passthru('exiftool -b -thumbnailImage '.$image_source_filename, $return_var);
$type=IMAGETYPE_JPEG;
$thumb_data = ob_get_contents();
ob_end_clean(); 
// 			exec('exiftool -b -thumbnailImage '.$image_source_filename, $thumb_data, $return_var);
			
//             error_log('Test --'.var_export($return_var, TRUE).'--');
//             foreach($thumb_data as $datum) error_log('Test---'.var_export($datum, TRUE).'---');
//             $thumb_data=implode('', $thumb_data);
        }
		
// 		if ($thumb_data!==false) { 
		if (!empty($thumb_data)) {
			// Cache
			{
				header('Content-type: ' . image_type_to_mime_type($type));
				header('Cache-Control: public') ;
				
				$mtime = filectime($image_source_filename);
				$gmdate_mod = gmdate('D, d M Y H:i:s', $mtime) . ' GMT';

				// exit($fichier_date."Last-Modified: $gmdate_mod + ".$HTTP_IF_MODIFIED_SINCE) ;
				if (isset($HTTP_IF_MODIFIED_SINCE))
				{
					$if_modified_since = preg_replace('/;.*$/', '', $HTTP_IF_MODIFIED_SINCE);

					if ($if_modified_since == $gmdate_mod) {
						header("HTTP/1.0 304 Not Modified");
	// 					exit;
					}
				}
				//exit($fichier_date."Last-Modified: $gmdate_mod") ;
				header("Last-Modified: $gmdate_mod");
			}
	// // 		$thumb_filename = urldecode('_data/i/galleries/'.$matches[1].'-'.$matches[2].'.'.$matches[3]);

			// Si le fichier miniature n'existe pas OU s'il est plus ancien que l'original
	// // 		if (!file_exists($thumb_filename) || filectime($thumb_filename)<filectime($image_source_filename)) {
				// le fichier miniature est créé OU mis à jour à partir de la miniature des données EXIF de l'original
			if($matches[2]=='sq') {
				$thumb_ressource = imageCreateFromString($thumb_data);
				$tw = imagesx($thumb_ressource);
				$th = imagesy($thumb_ressource);
				$tm = min($tw, $th);
				
// 				$thumb_ressource = change_border_color($thumb_ressource, 51,51,51 );
				
//					$rect = array(0, 0, 120, 120) ;
// 				$thumb_ressource = imagecrop ( $thumb_ressource , $rect ) 
				$square_ressource = imagecreatetruecolor ( 120 , 120 );
				$couleur = imagecolorallocate($square_ressource, 51, 51, 51); // gris
				imagefill($square_ressource, 0, 0, $couleur);
				
				if ( $th < $tw ) {
// 						 imagecopyresampled ( resource $dst_image , resource $src_image , 
// 											 			int $dst_x , int $dst_y , int $src_x , int $src_y , 
// 														int $dst_w , int $dst_h , int $src_w , int $src_h ) : bool
					if ( $th < 40 ) {
						imagecopyresampled ( $square_ressource , $thumb_ressource , 0 , (120-3*$th)/2 , 0 , 0 , ($tw-120)/2 , $th , ($tw-120)/2 , $th);
						imagecopyresampled ( $square_ressource , $thumb_ressource , 0 , (120-3*$th)/2 + $th , ($tw-120)/2 , 0 , 120 , $th , 120 , $th);
						imagecopyresampled ( $square_ressource , $thumb_ressource , 120-($tw-120)/2 , (120-3*$th)/2 + 2*$th , 120+($tw-120)/2 , 0 , ($tw-120)/2 , $th , ($tw-120)/2 , $th);
					}
					elseif ( $th < 60 ) {
						imagecopyresampled ( $square_ressource , $thumb_ressource , 0 , (120-2*$th)/2 , ($tw-120)/2 , 0 , 120 , $th , 120 , $th);
						imagecopyresampled ( $square_ressource , $thumb_ressource , 0 , 120/2 , 0 , 0 , ($tw-120)/2 , $th , ($tw-120)/2 , $th);
						imagecopyresampled ( $square_ressource , $thumb_ressource , 120-($tw-120)/2 , 120/2 , 120+($tw-120)/2 , 0 , ($tw-120)/2 , $th , ($tw-120)/2 , $th);
					}
					else {
						imagecopyresampled ( $square_ressource , $thumb_ressource , 0 , (120-$th)/2 , ($tw-120)/2 , 0 , 120 , $th , 120 , $th);
					}
				}
				else {
					if ( $tw < 40 ) {
						imagecopyresampled ( $square_ressource , $thumb_ressource , (120-3*$tw)/2 , 0 , 0 , 0 , $tw , ($th-120)/2 , $tw , ($th-120)/2);
						imagecopyresampled ( $square_ressource , $thumb_ressource , (120-3*$tw)/2 + $tw , 0 , 0 , ($th-120)/2 , $tw , 120 , $tw , 120);
						imagecopyresampled ( $square_ressource , $thumb_ressource , (120-3*$tw)/2 + 2*$tw , 120-($th-120)/2 , 0 , 120+($th-120)/2 , $tw , ($th-120)/2 , $tw , ($th-120)/2);
					}
					elseif ( $tw < 60 ) {
						imagecopyresampled ( $square_ressource , $thumb_ressource , (120-2*$tw)/2 , 0 , 0 , ($th-120)/2 , $tw , 120 , $tw , 120);
						imagecopyresampled ( $square_ressource , $thumb_ressource , 120/2 , 0 , 0 , 0 , $tw , ($th-120)/2 , $tw , ($th-120)/2);
						imagecopyresampled ( $square_ressource , $thumb_ressource , 120/2 , 120-($th-120)/2 , 0 , 120+($th-120)/2 , $tw , ($th-120)/2 , $tw , ($th-120)/2);
					}
					else {
						// un exemple : un carré de 120 acceuille un carré de 160
						imagecopyresampled ( $square_ressource , $thumb_ressource , (120-$tw)/2 , 0 , 0 , ($th-120)/2 , $tw , 120 , $tw , 120);
					}
				};
				imagejpeg ( $square_ressource, NULL, 75);
// 				imagejpeg ( $square_ressource , $thumb_filename, 75);
			}
			else {
// 				$thumb_ressource = imageCreateFromString($thumb_data);
// 				imagejpeg ( $thumb_ressource , $thumb_filename, 75);

// 				$thumb_ressource = imageCreateFromString($thumb_data);
// 				$thumb_ressource = change_border_color($thumb_ressource, 34,34,34 );
// 				imagejpeg ( $thumb_ressource, NULL, 75);
				
				echo $thumb_data;

// 				file_force_contents($thumb_filename, $thumb_data);

// 				file_put_contents($thumb_filename, $thumb_data);
// 				file_put_contents(dirname($_SERVER["SCRIPT_FILENAME"]).'/'.$thumb_filename, $thumb_data);
			}

	// // 		}
			
	// 	error_log('yes - '.$_SERVER['QUERY_STRING']);
			$exif = @exif_read_data($image_source_filename, null, true);
			$canonCase = isset($exif['IFD0']['Make']) && $exif['IFD0']['Make']=='canon2' ;
			if($canonCase) {
				exec('exiftool -make=Canon '.$image_source_filename);
			}
			exit;
		}
		else {
			$exif = @exif_read_data($image_source_filename, null, true);
			$canonCase = isset($exif['IFD0']['Make']) && $exif['IFD0']['Make']=='Canon' ;
			if($canonCase) {
				exec('exiftool -make=canon2 '.$image_source_filename);
// 				goto debut;
			}
			else {
				// no thumbnail available, handle the error here
				echo implode(', ', array('Thumbnail not yet available', $width, $height, $type, $image_source_filename));

                if (empty($thumb_data)) {
                    $image_source_path = dirname($_SERVER['SCRIPT_FILENAME']).'/'.$image_source_filename ;
//                     echo "convert -define jpeg:size=320x320 $image_source_path -thumbnail '160x160>' - | exiftool -overwrite_original '$image_source_path' -thumbnailimage'<=-' -v0" ;
//                     exec("convert -define jpeg:size=320x320 $image_source_path -thumbnail '160x160>' - | exiftool -overwrite_original '$image_source_path' -thumbnailimage'<=-' -m -q -q");
//                     exec('exiftran -g -ip '.$image_source_path);
                }
                exit;
			}
		}
	}
	else {
		echo $image_source_filename."\n";
// 	error_log('no - '.$_SERVER['QUERY_STRING']);
		exit;
	}
}
// 2022-06-06 le serveur dispose de la fibre à Figeac (1Gbit descendant et 500Mbit montant, soit 100 fois plus rapide qu'avec l'ADSL !)
// L'idée est de bricoler le mécanisme d'affichage des grandes images sans alourdir le stockage serveur, en servant le fichier original
// Une ligne de style a été ajoutée dans le pied de page afin de contenir la taille de l'image à 100% de la largeur du conteneur.
// Il reste un bug pour la navigation à la souris en cliquant sur l'image map qui est dimensionné sur une image plus petite. Contournement via le clavier ou les boutons de navigation.
elseif(1 && (
  preg_match('/^\/galleries\/(.*)\-(la|xl|xx)\.(jpg|JPG)$/', $_SERVER['QUERY_STRING'], $matches) ||
  preg_match('/^\/galleries\/(.*)\-(la|xl|xx)_(jpg|JPG)$/', key($_GET), $matches)
)) {
	$image_source_filename = /*utf8_decode*/(urldecode('galleries/'.$matches[1].'.'.$matches[3]));
	if (file_exists($image_source_filename) || (($image_source_filename=str_replace('_', ' ', $image_source_filename)) && file_exists($image_source_filename)))
	{
// 		$fichier_date = $image_source_filename ;
// debut:
				
// 		if ($thumb_data!==false) { 
// 		if (!empty($image_data)) {
			// Cache
			{
				header('Content-type: ' . 'image/jpeg');
				header('Cache-Control: public') ;
				
				$mtime = filectime($image_source_filename);
				$gmdate_mod = gmdate('D, d M Y H:i:s', $mtime) . ' GMT';

				// exit($fichier_date."Last-Modified: $gmdate_mod + ".$HTTP_IF_MODIFIED_SINCE) ;
				if (isset($HTTP_IF_MODIFIED_SINCE))
				{
					$if_modified_since = preg_replace('/;.*$/', '', $HTTP_IF_MODIFIED_SINCE);

					if ($if_modified_since == $gmdate_mod) {
						header("HTTP/1.0 304 Not Modified");
	// 					exit;
					}
				}
				//exit($fichier_date."Last-Modified: $gmdate_mod") ;
				header("Last-Modified: $gmdate_mod");
			}
// 			exit;
// 		}
		
		echo file_get_contents($image_source_filename) ;
		exit ;
	}
	else {
		echo $image_source_filename."\n";
// 	error_log('no - '.$_SERVER['QUERY_STRING']);
		exit;
	}

}
else {
//  let the process run
// 	error_log('?? - '.$_SERVER['QUERY_STRING']);
}

// L'image peut contenir une paire de marges opposées noires, soit horizontales, soit verticales
// Le but est de parcourir l'image par ligne en partant du bas, sitôt qu'un pixel non noir apparait, 
// on en déduit si l'image possède des bandes et si elles sont verticales. 
// La suite consiste par sysmétrie à raccourcir le temps de balayage de l'image
// pb car certaines images sont mal analysées. Carituralement boule blanche sur fond noir.
// Source : https://stackoverflow.com/questions/1548534/php-replace-colour-within-image
function change_border_color($img_rsc, $r, $g, $b) {
	// Open input and output image
	$src = $img_rsc;
	$fill_pix_array = array( $r,$g,$b );
// 	$out = ImageCreateTrueColor(imagesx($src),imagesy($src)) or die('Problem In Creating image');
	$out = $img_rsc;
	$seuil = 16; // pb car certaines images sont mal analysées. Carituralement boule blanche sur fond noir.

	$in_image = FALSE;
	// scan image pixels
	// 
	for ($x = 0; $x < imagesx($src); $x++) {
		for ($y = 0; $y < imagesy($src); $y++) {
			$src_pix = imagecolorat($src,$x,$y);
			$src_pix_array = rgb_to_array($src_pix);

			if (! $in_image) {
				// check for chromakey color
				// if ($src_pix_array[0] == 0 && $src_pix_array[1] == 0 && $src_pix_array[2] == 0) {
				if ($src_pix_array[0] < $seuil && $src_pix_array[1] < $seuil && $src_pix_array[2] < $seuil) {
					$src_pix_array = $fill_pix_array;
				}
				else { // on entre dans l'image
					$in_image = TRUE;
					if ($x == 0) {
						$is_vertical = FALSE;
						$margin_width = $y+1; 
					}
					else {
						$is_vertical = TRUE;
						$margin_width = $x+1; 
					}
				}
				imagesetpixel($out, $x, $y, imagecolorallocate($out, $src_pix_array[0], $src_pix_array[1], $src_pix_array[2]));
			}
			else {
				if (!$is_vertical && ( $y<$margin_width || imagesy($src)-$margin_width<$y ) ) {
					imagesetpixel($out, $x, $y, imagecolorallocate($out, $fill_pix_array[0], $fill_pix_array[1], $fill_pix_array[2]));
				} 
				elseif ($is_vertical && ( $x<$margin_width || imagesx($src)-$margin_width<$x ) ) {
					imagesetpixel($out, $x, $y, imagecolorallocate($out, $fill_pix_array[0], $fill_pix_array[1], $fill_pix_array[2]));
				} 
			}
		}
	}

	
	return $out;
}

// split rgb to components
function rgb_to_array($rgb) {
	$a[0] = ($rgb >> 16) & 0xFF;
	$a[1] = ($rgb >> 8) & 0xFF;
	$a[2] = $rgb & 0xFF;

	return $a;
}

?>
