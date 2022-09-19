<?php

	session_start();	#sesja w dokumencie bedzie działać [przeyslanie danych do innych plikow php]

	if((!isset($_POST['login'])) || (!isset($_POST['haslo']))) #jesli (nie jest wpisany) nie jest ustawiony (!isset) login lub haslo wtedy przez header do inedex.php i zamkywakmy plik exit()
	{
		header('Location: index.php');
		exit();
	}

	require_once "connect.php"; #dołączenie pliku connect.php
	mysqli_report(MYSQLI_REPORT_STRICT);

	try 
	{
		$polaczenie = new mysqli($host, $db_user, $db_password, $db_name);
		if($polaczenie->connect_errno!=0)
		{
			throw new Exception(mysqli_connect_errno());
			//echo "Error:".$polaczenie->connect_errno; //stare roziwąznie do polaczenie=@new mysqli
		}
		else
		{
			$login = $_POST['login'];
			$haslo = $_POST['haslo'];

			$login=htmlentities($login,ENT_QUOTES,"UTF-8");  #ENT_QUTOES zamienia na encje apostrofy i cudzusłów, UTF-8 okresla zestaw zanków
			//$haslo=htmlentities($haslo,ENT_QUOTES,"UTF-8"); nie uzywamy poniewaz haszaowaliśmy haslo
			

			if ($rezultat = $polaczenie->query(sprintf("SELECT * FROM uzytkownicy 
			WHERE user='%s'",// AND pass='%s'", hashowalismy haslo
			mysqli_real_escape_string($polaczenie,$login))))
			//mysqli_real_escape_string($polaczenie,$haslo)))) hashowalimsy haslo
			#spritnf() wprowadza porządek i pilnuje typów danych, tam gdzie jest %s infromauje ze tam wystepuje zmienna typu string, podana po przecinku, i kolejny analogicznie
			#mysqli_real_escape_string funkcja napisana w celu ochorny przed wstrzykiwaniem SQL
			{
				$ilu_userow = $rezultat->num_rows;
				if($ilu_userow>0) 
				{
					$wiersz=$rezultat->fetch_assoc(); #fetch_assoc [tablica asocjacyjna] zwraca nazwy kolumn z bazy zamiast ich indeksy

					if(password_verify($haslo,$wiersz['pass']))
					{
						$_SESSION['zalogowany']=true;
						$_SESSION['id']= $wiersz['id'];
						$_SESSION['user']= $wiersz['user'];
						$_SESSION['drewno']= $wiersz['drewno'];
						$_SESSION['kamien']= $wiersz['kamien'];
						$_SESSION['zboze']= $wiersz['zboze'];
						$_SESSION['email']= $wiersz['email'];
						$_SESSION['dnipremium']= $wiersz['dnipremium'];

						unset($_SESSION['blad']); #usun sesje blad
						$rezultat->free_result(); #do pozbycia sie z pamięci wyniku rezultat
						header('Location:gra.php'); #przekierowanie zmiennych do pliku gra.php
						#tutaj po header nie dajemy exit poniewaz na koncu skryptu mamy close() który by sie nie wykonał jesli dalibyśmy exit()
					}
					
					else
					{
						$_SESSION['blad'] = '<span style="color:red">Nieprawidłowy login lub hasło!</span>';
						header('Location: index.php');
						
					}
				} 
			
				else 
				{
					$_SESSION['blad'] = '<span style="color:red">Nieprawidłowy login lub hasło!</span>';
					header('Location: index.php');
					
				}
			}
			else
			{
				throw new Exception($polaczenie->error);
			}
			
			$polaczenie->close();
		}
	}
	catch(Exception $e)
	{
		echo '<span style="color:red;">Błąd serwera! Przepraszamy za niedogodności i prosimy o wizytę w innym terminie!</span>';
		echo '<br />Informacja developerska: '.$e;
	}
?>