<!DOCTYPE html>
<html>

<script src="http://code.jquery.com/jquery-latest.js"></script>
<?php session_start(); ?>

<script type="text/javascript">
	var wordsArray = new Array();
	var word;
	var gameStatus = 0;            //Guess number in round
	var wordCount = 0;              //how many words are left
	var puzzlesWonInRound = 0;
	var puzzlesLostInRound = 0;
	var totalPuzzlesWon = 0;
	var totalPuzzlesLost = 0;
	var totalRoundsPlayed = 0;
	var totalPuzzlesPlayed = 0;
	var totalRoundsWon = 0;
	var wonRound = false;
	var roundOver = false;
	var timerCount = 10;
	var timer;
	var timerStart = false;
	
	//request words from php script
	function requestWords() {
		wordsArray.length = 0;
		
		if (window.XMLHttpRequest) {
			xmlHttpReq = new XMLHttpRequest();
			
			if (xmlHttpReq.overrideMimeType) {
				xmlHttpReq.overrideMimeType('text/xml');
			}
		} else if (window.ActiveXObjet) {
			try {
				xmlHttpReq = new ActiveXObject("Msxml2.XMLHTTP");
			} catch (e) {
				try {
					xmlHttpReq = new ActiveXObject("Microsoft.XMLHTTP");
				} catch (e) {}
			}
		}
		
		if (!xmlHttpReq) {
			alert("FAILED");
			return false;
		}
		
		xmlHttpReq.open('POST', 'getwords.php', true);
		xmlHttpReq.setRequestHeader('Content-Type', 'application/x-www-form-urlencoded');
		xmlHttpReq.onreadystatechange = function() {getWords(xmlHttpReq);};
		xmlHttpReq.send();
	}
	
	//get words in response to requestWords function
	function getWords(httpRequest) {
		if (httpRequest.readyState == 4 && httpRequest.status == 200) {
			wordsArray = JSON.parse(httpRequest.responseText);
			setTable();
		} 
		
		if (httpRequest.status != 200) {
			alert ('Problem getting words');
		}
	}
	
	function setTable() {
		word = wordsArray[wordCount];
		alert(word);
		var letter = word.charAt(0).toUpperCase();
		
		var table = document.getElementById("gameboardTable");
		var row = table.rows[0];
		var firstCell = row.cells[0];
		
		firstCell.innerHTML = letter;
		firstCell.style.color = "red";
		
		if (timerStart == false ) {
			startTimer();
			timerStart = true;
		}
	}
	
	function clearTable() {
		var table = document.getElementById("gameboardTable");
		
		for (var i = 0; i < 6; i++) {
			var row = table.rows[i];
			for (var j = 0; j < 5; j++) {
				var cell = row.cells[j];
				cell.innerHTML = "&nbsp";
				cell.style.color = "black";
			}
		}
	}
	
	function process() {
	
		if (roundOver) {
			return;
		}
		
		//resetTimer();
		
		var newGuess = document.getElementById("guess").value;
		if (gameStatus < 5) {
			newGuess = newGuess.toUpperCase();
			word = word.toUpperCase();
			var theLetter;
			
			var table = document.getElementById("gameboardTable");
			var row; var cell;
			var a = new Array();
			var b = new Array();
			
			for (var i = 0; i < 5; i++) {
				a[i] = 1;
				b[i] = 1;
			}
			
			for (var i = 0; i < 5; i++) {
				if (newGuess.charAt(i) == word.charAt(i)) {
					theLetter = word.charAt(i).toUpperCase();
					table.rows[gameStatus + 1].cells[i].innerHTML = theLetter;
					table.rows[gameStatus + 1].cells[i].style.color = "red";
					
					a[i] = -1;
					b[i] = 0;
				}
			}
			
			wordMatch = true;
			for (var i = 0; i < 5; i++) {
				if (a[i] != -1) {
					wordMatch = false;
					i = 10;
				}
			}
			
			if (wordMatch) {
				wonPuzzle();
			} else {
				
				for (var i = 0; i < 5; i++) {
					if (a[i] == 1) {
						wordMatch = false;
						
						for (var j = 0; j < 5; j++) {
							if (b[j] == 1) {
								if (newGuess.charAt(i) == word.charAt(j)) {
									theLetter = newGuess.charAt(i).toUpperCase();
									table.rows[gameStatus + 1].cells[i].innerHTML = theLetter;
									table.rows[gameStatus + 1].cells[i].style.color = "blue";
									
									b[j] = 0;
									j = 10;
									wordMatch = true;
								}
							}
						}
						
						if (!wordMatch) {
							theLetter = newGuess.charAt(i).toLowerCase();
							table.rows[gameStatus + 1].cells[i].innerHTML = theLetter;
							table.rows[gameStatus + 1].cells[i].style.color = "black";
						}
					}
				}
				gameStatus++;
				
				if(gameStatus == 5) {
					lostPuzzle();
				}
			}
		} else {
			lostPuzzle();
		}
		document.gameboard.guess.value = "";
		document.getElementById('guess').focus();
		return false;	
	}
	
	function lostPuzzle() {
		if (wordCount < 4) {
			if (puzzlesLostInRound < 2) {
				alert('Incorrect, the word was ' + word + '. Ready for puzzle ' + (wordCount + 2) + '?');
				puzzlesLostInRound++;
				totalPuzzlesLost++;
				newPuzzle();
			} else {
				alert('You lost :( The word was ' + word + ". You have lost the game. Press 'New Game' to start a new round.");
				wonRound = false;
				puzzlesLostInRound++;
				totalPuzzlesLost++;
				gameOver();
			}
		} else {
			alert('You lost :( the word was ' + word + ". You have lost the game. Press 'New Game' to start a new round.");
			wonRound = false;
			puzzlesLostInRound++;
			totalPuzzlesLost++;
			gameOver();
		}
	}
	
	function wonPuzzle() {
		if (wordCount < 4) {
			if (puzzlesWonInRound < 2){
				alert('Correct! Ready for puzzle ' + (wordCount + 2) + '?');
				puzzlesWonInRound++;
				totalPuzzlesWon++;
				newPuzzle();
			} else {
				alert('You won! You have won the round, press New Game to start a new round');
				puzzlesWonInRound++;
				totalPuzzlesWon++;
				wonRound = true;
				gameOver();
			}
		} else {
			alert('You won! You have won the round, press New Game to start a new round');
			puzzlesWonInRound++;
			totalPuzzlesWon++;
			wonRound = true;
			gameOver();	
		}
	}
	
	function newPuzzle() {
		wordCount++;
		totalPuzzlesPlayed++;
		gameStatus = 0;
		clearTable();
		setTable();
		resetTimer();
	}
	
	function gameOver() {
		totalPuzzlesPlayed++;
		totalRoundsPlayed++;
		roundOver = true;
		
		clearInterval(timer);
		timerCount = 10;
		var tmr = document.getElementById("timer");
		tmr.innerHTML = timerCount;
		
		if (wonRound == true) {
			totalRoundsWon++;
		}
		
		document.getElementById('roundsPlayed').innerHTML = "Rounds Played: " + totalRoundsPlayed;
		document.getElementById('roundsWon').innerHTML = "Rounds Won: " + totalRoundsWon;
		document.getElementById('puzzlesPlayed').innerHTML = "Puzzles Played: " + totalPuzzlesPlayed;
		document.getElementById('puzzlesWon').innerHTML = "Puzzles Won: " + totalPuzzlesWon;
		
		localStorage.setItem("rounds_played", JSON.stringify(totalRoundsPlayed));
		localStorage.setItem("rounds_won", JSON.stringify(totalRoundsWon));
		localStorage.setItem("puzzles_played", JSON.stringify(totalPuzzlesPlayed));
		localStorage.setItem("puzzles_won", JSON.stringify(totalPuzzlesWon));
	}
	
	function resetTimer() {
		clearInterval(timer);
		timerCount = 10;
		var tmr = document.getElementById("timer");
		tmr.innerHTML = timerCount;
		startTimer();
	}
	
	function startTimer() {
		timer = setInterval(function() {
			if(timerCount == 0) {
				stopTimer();
			}
			timerCount--;
			var tmr = document.getElementById("timer");
			tmr.innerHTML = timerCount;
		} , 1000);
	}
	
	function stopTimer() {
		clearInterval(timer);
		timerCount = 10;
		var tmr = document.getElementById("timer");
		tmr.innerHTML = timerCount;
		
		var table = document.getElementById("gameboardTable");
		var row = table.rows[gameStatus + 1];
		for (var i = 0; i < 5; i++) {
			if (gameStatus == 0 && i == 0) {
			} else {
				var currCell = row.cells[i];
				currCell.innerHTML = "X";
				currCell.style.color="green";
			}
		}
		gameStatus++;
		
		if (gameStatus == 5) {
			lostPuzzle();
		} else {
			startTimer();
		}
	}			
	
	function startGame() {
		roundOver = false;
		timerStart = false;
		wordCount = 0;
		gameStatus = 0;
		puzzlesLostInRound = 0;
		puzzlesWonInRound = 0;
		clearTable();
		
		requestWords();
	}
	
	function readLocalData() {
		if (typeof(Storage) !== "undefined") {
			var user_name = localStorage.getItem("user_name");
			if (user_name) {
				var username = JSON.parse(localStorage.user_name);
				alert('Hello ' + username + '! Let\'s get started!');
				setStats();
			 } else {
				var username = prompt("Please enter your name to get started");
				localStorage.setItem("user_name", JSON.stringify(username));
				alert('Hello ' + username + '! Let\'s get started!');
			}
		}
	}
	
	function setStats() {
		if (typeof(Storage) !== "undefined") {
			var puzzles_played = localStorage.getItem("puzzles_played")
			var puzzles_won = localStorage.getItem("puzzles_won");
			var rounds_won = localStorage.getItem("rounds_won");
			var rounds_played = localStorage.getItem("rounds_played");
			
			if (puzzles_played && puzzles_won && rounds_won && rounds_played) {
				puzzles_played = JSON.parse(localStorage.puzzles_played);
				puzzles_won = JSON.parse(localStorage.puzzles_won);
				rounds_played = JSON.parse(localStorage.rounds_played);
				rounds_won = JSON.parse(localStorage.rounds_won);
			} else {
				puzzles_played = 0;
				puzzles_won = 0;
				rounds_played = 0;
				rounds_won = 0;
			}
			
			totalPuzzlesPlayed = puzzles_played;
			totalPuzzlesWon = puzzles_won;
			totalRoundsPlayed = rounds_played;
			totalRoundsWon = rounds_won;
			
			document.getElementById('roundsPlayed').innerHTML = "Rounds Played: " + rounds_played;
			document.getElementById('roundsWon').innerHTML = "Rounds Won: " + rounds_won;
			document.getElementById('puzzlesPlayed').innerHTML = "Puzzles Played: " + puzzles_played;
			document.getElementById('puzzlesWon').innerHTML = "Puzzles Won: " + puzzles_won;	
			
		}
	}
	

</script>

<body>

<title> Lingo! </title>
<div align = "center">
<h1>Lingo!</h1>

<form name = "gameboard" onsubmit = "return process();">
	<table id = "gameboardTable" border = "1" cellpadding = "25">
		
		<button type = 'button' onclick = 'startGame()'>New Game</button>
		</br></br>
		
		Enter Guess: <input type = "text" id = "guess" name = "guess" value = "">
		<input type = "button" value = "Guess" onclick = 'process()'>
		</br>Time to guess: <div id = "timer">10</div>
		
		<tr><td>&nbsp</td><td>&nbsp</td><td>&nbsp</td><td>&nbsp</td><td>&nbsp</td></tr>
		<tr><td>&nbsp</td><td>&nbsp</td><td>&nbsp</td><td>&nbsp</td><td>&nbsp</td></tr>
		<tr><td>&nbsp</td><td>&nbsp</td><td>&nbsp</td><td>&nbsp</td><td>&nbsp</td></tr>
		<tr><td>&nbsp</td><td>&nbsp</td><td>&nbsp</td><td>&nbsp</td><td>&nbsp</td></tr>
		<tr><td>&nbsp</td><td>&nbsp</td><td>&nbsp</td><td>&nbsp</td><td>&nbsp</td></tr>
		<tr><td>&nbsp</td><td>&nbsp</td><td>&nbsp</td><td>&nbsp</td><td>&nbsp</td></tr>
		</table>
		
</form>

	<div id = 'roundsPlayed'>Rounds Played: 0</div>
	<div id = 'roundsWon'>Rounds Won: 0</div>
	<div id = 'puzzlesPlayed'>Puzzles Played: 0</div>
	<div id = 'puzzlesWon'>Puzzles Won: 0</div>
	
	<script type = "text/javascript">
		readLocalData();
	</script>
</body>
</html>
