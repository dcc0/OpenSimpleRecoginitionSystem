<?php

	require_once  	'classes/Class_Convert_Image_To_01.php';
	require_once	'classes/Class_Hopfield_Network_Matrix.php';
	require_once	'form.html';


	$img_dir = 'img'; //путь к изображениям
	$image = 'm.png'; //изображение

	if (!isset($_GET['go']) && empty($_GET['image']))
	{
	print 'Пример изображения ';
	print '<img src=\'img/m.png\'>';
	print '<br/>';
	$image="$img_dir/$image";
	}

	if (isset($_GET['go']) && !empty($_GET['image']))
	{
	$image=$_GET['image'];
	print 'Изображение: ';
	print "<img src=\"$image\">";
	print '<br/>';
	}


	/*Преобразование изображений*/
	/*Изображение в формате png или jpg. Должно быть обязательно чёрно-белым.
	 *Выделяемая область должна бать чёрной */

	/*Определяем формат изображения*/
	if (preg_match("/\.jpg/", $image))  {
	$my_image = imagecreatefromjpeg("$image");
	}

	if (preg_match("/\.png/", $image))  {
	$my_image = imagecreatefrompng("$image");
	}

	if (preg_match("/\.gif/", $image))  {
	$my_image = imagecreatefromgif("$image");
	}

	/*Высота и ширина образца и искомого вектора*/
	$widthMyImg = imagesx($my_image);
	$widthVector = imagesx(imagecreatefrompng('img/m.png'));


	/*Новый объект класса*/
	$converted_image_to_bin = new Convert_Image_To_01;
	/*Вызов метода клааса с перемнной*/
	print '<strong>Цифровый контур изображения</strong> <br/>';
	$converted_image_to_bin->imgToBin($my_image);
     /*Транспонирует матрицу*/
     print '<strong>Траснпонируем контур </strong><br/>';
	$array_matrix=$converted_image_to_bin->transformMatrix();


/*----------------------------------------------------------------------------------------*/
/*------------------------Заменим 01 на 1,-1 в массиве------------------------------------*/
	foreach ($array_matrix as $k => $val)
	{
		if ($val == '1')
		$pattern[$k] = '1';
	    if ($val == '0')
	    $pattern[$k] = '-1';
	    $temp=$pattern[$k];
	    //print $pattern[$k] .',';
	    $save_vector.= $temp. ',';
	    //if ($k%30==1)
	    //print '<br/>';
	}
/*----------------------------------------------------------------------------------------*/
	$filevectors="vectors.txt";
	/*Обрежем запятую в конце*/
	//$save_vector=substr($save_vector, 0, -1);

	/*Запоминине вектора*/
	if (isset($_POST['save'])) {
	file_put_contents($filevectors, $save_vector . "\r\n", FILE_APPEND);
	}


	/*Поиск вектора. Сеть Хопфилда*/

	/*Векторы - образцы сети*/
	/*$vector[0]=array(-1,1,-1,1);*/

	$array_vectors=file($filevectors);
	foreach($array_vectors as $k=> $value)
	 {
		$vector[$k] = explode(",", $value);
	}


	/*Искомый вектор
	$pattern=array(1,-1,1,-1);*/


	$new_matrix = new Hopfield_Network_Matrix;
	//$new_matrix->createMatrix($vector);
	$filename="matrixnew.txt";

	/*Пересчитаем матрицу*/
	if (isset($_POST['formmatrix'])) {
	/*Передаём векторы в класс*/
	$new_matrix_array=$new_matrix->createMatrix($vector);
	$data = serialize($new_matrix_array);
	file_put_contents($filename, $data);
	$data = file_get_contents($filename);
	$new_arr = unserialize($data);
	}

	/*Читаем из файла предварительно посчитанную матрицу*/
	if(!isset($_POST['formmatrix']))
	{
	$data = file_get_contents($filename);
	$new_arr = unserialize($data);
	}


	/*Реузльтат умножения образца на матрицу и применение функции активации*/
	$next_result[0]=$new_matrix->preCountMatrix($pattern, $new_arr);
	$result=array();
	$result[]=$next_result[0];
	$new_result=array();


   /*Выводим цифровой образ хранимого сетью вектора*/
	/*Вызов в цикле метода*/

	print '<br/>Печатаем возможные образы (векторы умножены на матрицу)<br/>';

	for($i=1; $i < 5; $i++)

	{

		$next_result[$i] = 	$new_matrix->checkPattern($next_result[$i-1]);
		$result[] = $next_result[$i];

	}



	for ($j=0; $j  < count($vector); $j++) {

		/*Посчитаем количество схождений текущго состояния функции активации и образцов в массиве.
		* Найдем все схождения. Это позволит определить вес.*/
		foreach ($result as $key => $arr)

		{

		$new_result[$j][]=count(array_intersect_assoc($arr, $vector[$j]));

		}

			/*Печатаем текущее состояние функции активации*/
			foreach ($next_result[$i-1] as $k => $val)
	{

			if ($k%$widthVector+1==1)
			print '<br/>';


			if ($val == '-1')
			print '0';

			if ($val == '1' && $k < 900)
			print '1';


	}
			print '<br/>';



	}
	
	

			//print_r($new_result);
			$vector_number=array_search(max($new_result), $new_result);

			/*Печатаем результаты*/
			print '<br/><strong> Печатаем наиболее вероятный образец </strong> <br/>';
			print '<br/> Количество схождений образца с искомым вектором ' . max(max($new_result)). '<br/>';
			print '<br/> Вектор номер ' .$vector_number.'<br/>';

			foreach ($vector[$vector_number] as $k => $val)
			{

			if ($k%$widthVector+1==1)
			print '<br/>';

			if ($val == '-1')
			print '<strong>0</strong>';

			if ($val == '1')
			print '<strong>1</strong>';
			}
			print '<br/>';


?>
