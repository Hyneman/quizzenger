function Gamification(){
	var self = this;
	var gameLobbyTimer;

	this.initialize = function(){
		//GameLobby
		self.initTableNewGames();
		self.gameLobbyTimer();
		//GameStart
		self.joinGameEvent();
		self.leaveGameEvent();
		self.startGameEvent();
		self.gameStartTimer();
		//GameReport
		self.gameReportTimer();
	};

	this.joinGameEvent = function(){
		$("#joinGame").click(function(){
			var gameId = self.getUrlParameter('gameid');

			$.ajax({
				url: "index.php?view=joinGame&type=ajax&gameid="+gameId,
				type: "GET",
				contentType: false,
				cache: false,
				processData:false,
				complete: function(data){
					if(data.responseJSON !== undefined && data.responseJSON.result == "success"){
						$("#leaveGame").parent().removeAttr('hidden');
						$("#joinGame").parent().attr('hidden', 'true');
					}
					else{
						alert("Something went wrong");
					}
				}
			});
		});
	}

	this.leaveGameEvent = function(){
		$("#leaveGame").click(function(){
			var gameId = self.getUrlParameter('gameid');

			$.ajax({
				url: "index.php?view=leaveGame&type=ajax&gameid="+gameId,
				type: "GET",
				contentType: false,
				cache: false,
				processData:false,
				complete: function(data){
					$("#joinGame").parent().removeAttr('hidden');
					$("#leaveGame").parent().attr('hidden', 'true');
				}
			});
		});
	}

	this.startGameEvent = function(){
		$("#startGame").click(function(){
			var gameId = self.getUrlParameter('gameid');

			$.ajax({
				url: "index.php?view=startGame&type=ajax&gameid="+gameId,
				type: "GET",
				contentType: false,
				cache: false,
				processData:false,
				complete: function(data){
					$("#startGame").val("Game gestartet");
				}
			});
		});
	}

	this.initTableNewGames = function(){
		$('#tableNewGame tbody').on( 'click', 'tr', function () {
			var x = $(this).find('input[type=radio]');
        	$(this).find('input[type=radio]').prop('checked', true);
    		$('#tableNewGame tbody > tr').removeClass('success');
        	$(this).addClass('success');
    	} );
	}

	this.gameStartTimer = function(){
		var gameId = self.getUrlParameter('gameid');
		if(! self.contains(document.URL, 'view=GameStart&gameid='+gameId)) return;

		window.setInterval(function(){
			$.ajax({
				url: "index.php?view=getGameStartInfo&type=ajax&gameid="+gameId,
				type: "GET",
				contentType: false,
				cache: false,
				processData:false,
				complete: function(data){
					if(data.responseJSON === undefined) return;
					var resp = data.responseJSON.data;

					//startGame
					if(resp.gameinfo.starttime != null){
						window.location.href = "index.php?view=GameQuestion&gameid=" + resp.gameinfo.game_id;
					}

					//updateMembers
					$('#participantCount').text(resp.members.length);
					$('#participantList').html('');
					$(resp.members).each(function(id, member){
						$('#participantList').append('<li>' + member.member +'</li>');
					});
				}
			});
		}, 2000);
	}

	this.gameLobbyTimer = function(){
		$('a[data-toggle="tab"]').on('shown.bs.tab', function (e){
			if(e.currentTarget.hash == "#generateQuiz"){
				//removeTimer
				clearInterval(gameLobbyTimer);
			}
			if(e.currentTarget.hash == "#gameLobby"){
				self.updateGameLobbyData();
				//setTimer
				gameLobbyTimer = window.setInterval(function(){
					self.updateGameLobbyData();
				}, 2000);
			}
		});
	}

	this.updateGameLobbyData = function(){

		$.ajax({
				url: "index.php?view=getGameLobbyData&type=ajax",
				type: "GET",
				contentType: false,
				cache: false,
				processData:false,
				complete: function(data){
					if(data.responseJSON === undefined) return;

					//openGames
					var table = $('#tableOpenGames').DataTable();
					table.rows().remove();
					var template = '#dot-openGameRow';
					$(data.responseJSON.data.openGames).each(function(id, game){
						game.duration = self.formatTime(game.duration);

						var tempHtml = (doT.template($(template).text()))(game);
						table.row.add($(tempHtml));
					});
					table.draw();

					//activeGames
					var table = $('#tableActiveGames').DataTable();
					table.rows().remove();

					if(data.responseJSON.data.activeGames.length > 0){
						$('#activeGamesPanel').removeAttr('hidden');

						var template = '#dot-activeGameRow';
						$(data.responseJSON.data.activeGames).each(function(id, game){
							game.duration = self.formatTime(game.duration);

							var tempHtml = (doT.template($(template).text()))(game);
							table.row.add($(tempHtml));
						});
						table.draw();
					}
					else{
						$('#activeGamesPanel').attr('hidden', 'true');
					}

				}
			});
	}

	this.gameReportTimer = function(){
		var gameId = self.getUrlParameter('gameid');
		if(! (self.contains(document.URL, 'view=GameQuestion&gameid='+gameId)
			|| self.contains(document.URL, 'view=GameSolution&gameid='+gameId)
			|| self.contains(document.URL, 'view=GameEnd&gameid='+gameId))) return;

		self.updateGameReport();

		window.setInterval(function(){
			self.updateGameReport();
		}, 1000);
	}

	this.updateGameReport = function(){
		var gameId = self.getUrlParameter('gameid');

		$.ajax({
				url: "index.php?view=getGameReport&type=ajax&gameid="+gameId,
				type: "GET",
				contentType: false,
				cache: false,
				processData:false,
				complete: function(resp){
					if(resp.responseJSON === undefined || resp.responseJSON.data == undefined) return;
					var data = resp.responseJSON.data;
					//set Countdown
					if(data.timeToEnd > 0 && data.gameInfo.endtime==null){
						var formatTimeToEnd = self.formatSeconds(data.timeToEnd);
						self.applyTemplate("dot-gameReportCountdown", {
							'progressCountdown' : data.progressCountdown,
							'formatTimeToEnd' : formatTimeToEnd
						}, "gameCountdown");
					}
					else{
						$('#gameCountdown').html('');
						//redirect to GameEnd view if not already on this view
						if(! self.contains(document.URL, 'view=GameEnd')){
							window.location.href = "index.php?view=GameEnd&gameid=" + data.gameInfo.game_id;
						}
					}

					$('#gameReport').html('');
					//set GameReport
					$(data.gameReport).each(function(id, report){
						var isCurrentUser = report.user_id == data.userId;

						var correct = 100/report.totalQuestions * report.questionAnsweredCorrect;
						var wrongCount = report.questionAnswered - report.questionAnsweredCorrect;
						var wrong = 100 / report.totalQuestions * wrongCount;
						var togo = 100 / report.totalQuestions * (report.totalQuestions - report.questionAnswered);
						var togoCount = report.totalQuestions - report.questionAnswered;

						var formatTimePerQuestion = self.formatSeconds(report.timePerQuestion);
						var formatTotalTimeInSec = self.formatSeconds(report.totalTimeInSec);
						self.appendTemplateToContainer("dot-gameReportRow", {
							'report' : report,
							'isCurrentUser' : isCurrentUser,
							'correct' : correct,
							'wrongCount' : wrongCount,
							'wrong' : wrong,
							'togo' : togo,
							'togoCount' : togoCount,
							'formatTimePerQuestion' : formatTimePerQuestion,
							'formatTotalTimeInSec' : formatTotalTimeInSec
						}, "gameReport");
					});
				}
			});
	}

	/*
	 * Returns a string like '1 Std 6 Min 5 Sek'
	* @param sec total seconds
	*/
	this.formatTime = function(time){
		var arr = time.split(':');
		if(arr.length != 3) return '';

		var hours = parseInt(arr[0]);
		var minutes = parseInt(arr[1]);
		var seconds = parseInt(arr[2]);
		return (hours > 0?hours+' Std ':'')+(minutes > 0?minutes+' Min ':'')+(seconds > 0?seconds+' Sek':'');
	}

		/*
	 * Returns a string like '1 Std 6 Min 5 Sek'
	* @param time mysql time like '23:10:59'
	*/
	this.formatSeconds = function(sec){
		var hours = parseInt(sec / 3600);
		sec = sec % 3600;
		var minutes = parseInt(sec / 60);
		var seconds = Math.round((sec % 60) * 100) / 100; //round on 2 decimals
		return (hours > 0?hours+' Std ':'')+(minutes > 0?minutes+' Min ':'')+(seconds > 0?seconds+' Sek':'');
	}

	this.applyTemplate = function(template, parameters, container) {
		container = "#" + container;
		template = "#" + template;
		$(container).html((doT.template($(template).text()))(parameters));
	};

	this.appendTemplateToContainer = function(template, parameters, container) {
		container = "#" + container;
		template = "#" + template;
		$(container).html($(container).html() + (doT.template($(template).text()))(parameters));
	};

	/*
	* Checks if obj1 contains obj2
	* @return Returns true if contains, else false
	*/
	this.contains = function(obj1, obj2){
		return obj1.indexOf(obj2) > -1;
	};

	this.getUrlParameter = function(sParam){
	    var sPageURL = window.location.search.substring(1);
	    var sURLVariables = sPageURL.split('&');
	    for (var i = 0; i < sURLVariables.length; i++)
	    {
	        var sParameterName = sURLVariables[i].split('=');
	        if (sParameterName[0] == sParam)
	        {
	            return sParameterName[1];
	        }
	    }
	}


}