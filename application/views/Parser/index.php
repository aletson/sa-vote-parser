<div class="jumbotron text-center">
    <h1>Vote Parser<small> (beta)</small></h1>
    <br/>
    <div class="form-inline" id="thread-id-form-single">
    <input class="form-control" type="text" id="thread-id" placeholder="Thread ID" />
        <button class="btn btn-default" id="get-votes-btn">Get Votes</button>
    </div>
</div>
    <div id="results" class="well" style="display:none;">

    </div>
<p>Vote Parser is picky! Some rules:</p>
<ul>
<li>No voting and unvoting in the same [b][/b] block. It won't work, unfortunately.</li>
<li>OP must use "[b]Day X Begins Now[/b]" (case insensitive) to start a new day (and wipe the votecount for the next day).</li>
<li>No nickname support yet, working on it.</li>
<li>Vote parsing is case sensitive for now.</li>

</ul>
<br/><br/><br/>
<div class="footer text-muted">

	
    <p><i>Vote parser created by Flying Leatherman. Special thanks to <a href="http://www.lavishbootstrap.com">Lavish Bootstrap.</a></i></p>
</div>

<script type="text/javascript">
    $(document).ready(function() {
        $('#get-votes-btn').click(function() {
            $('#results').fadeOut();
			var threadid = $('#thread-id').val();
            $.post('<?php echo base_url();?>parser/get_votes', {threadid: threadid})
                .done(function(data) {
                    data = $.parseJSON(data);
                    if(data.error === "no") {
						var vote_str = '';
						$.each(data.votecount, function(day_idx, day_votes) {
							var votesPerPlayer = new Object();
							var votePlayerStrings = new Object();
							var votePlayerHTML = new Object();
							$.each(day_votes, function(target, votes) {
								$.each(votes, function(postID, vote_data) {
									if(!votePlayerStrings[target]) {
										votePlayerStrings[target] = '';
										votePlayerHTML[target] = '';
									}
									if(votesPerPlayer[target]) {
										votesPerPlayer[target]++;
									} else {
										votesPerPlayer[target] = 1;
									}
									if (vote_data.type == 'unvote') { //Unvote
										votesPerPlayer[target]--;
										votePlayerStrings[target] += '[s][i][url=https://forums.somethingawful.com/showthread.php?threadid=' + threadid + '&perpage=40&pagenumber=' + vote_data.page + '#post' + vote_data.post + ']' + vote_data.name + '[/url][/i][/s], ';
										votePlayerHTML[target] += '<strike><i><a href="https://forums.somethingawful.com/showthread.php?threadid=' + threadid + '&perpage=40&pagenumber=' + vote_data.page + '#post' + vote_data.post + '">' + vote_data.name + '</a></i></strike>, ';
									} else if (vote_data.active == 'yes') { //Active vote
										votePlayerStrings[target] += '[b][url=https://forums.somethingawful.com/showthread.php?threadid=' + threadid + '&perpage=40&pagenumber=' + vote_data.page + '#post' + vote_data.post + ']' + vote_data.name + '[/url][/b], ';
										votePlayerHTML[target] += '<b><a href="https://forums.somethingawful.com/showthread.php?threadid=' + threadid + '&perpage=40&pagenumber=' + vote_data.page + '#post' + vote_data.post + '">' + vote_data.name + '</a></b>, ';
									} else { //Inactive vote
										votesPerPlayer[target]--;
										votePlayerStrings[target] += '[url=https://forums.somethingawful.com/showthread.php?threadid=' + threadid + '&perpage=40&pagenumber=' + vote_data.page + '#post' + vote_data.post + ']' + vote_data.name + '[/url], ';
										votePlayerHTML[target] += '<a href="https://forums.somethingawful.com/showthread.php?threadid=' + threadid + '&perpage=40&pagenumber=' + vote_data.page + '#post' + vote_data.post + '">' + vote_data.name + '</a>, ';
									}
								});
								votePlayerStrings[target] = votePlayerStrings[target].slice(0, -2); //Removes trailing comma.
								votePlayerHTML[target] = votePlayerHTML[target].slice(0, -2); //Removes trailing comma.
							});
							vote_str += '<div class="row"><div class="col-md-6"><pre>[b][u]Votecount for Day ' + day_idx + '[/u][/b]<br/>';
							sortedVotes = Object.keys(votesPerPlayer).sort(function(a,b) {return votesPerPlayer[b]-votesPerPlayer[a]});
							
							$.each(sortedVotes, function(i,v) {
								vote_str += '[b]' + v + ' (' + votesPerPlayer[v] + '): [/b]' + votePlayerStrings[v] + '<br/>';
							});
							vote_str += '</pre></div><div class="col-md-6"><b><u>Votecount for Day ' + day_idx + '</u></b><br/>';
							$.each(sortedVotes, function(i,v) {
								vote_str += '<b>' + v + ' (' + votesPerPlayer[v] + '): </b>' + votePlayerHTML[v] + '<br/>';
							});
							vote_str += '</div></div><hr/>';
						});
						$('#results').html(vote_str);
                    } else {
                        $('#results').html('We encountered an error:<br/>' + data.error_text);
                    }

                })
                .fail(function(jqXHR) {
                    $('#results').text('Something went wrong! We\'ll do our best to get this fixed quickly.');
                })
                .always(function() {
                    $('#results').fadeIn();
                });
        });
    });
</script>