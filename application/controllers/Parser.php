<?php
class Parser extends MY_Controller {
    function __construct() {
        parent::__construct();
        $this->load->model('Awful');

    }

    function index() {
        $data = new stdClass;
        $data->title = 'Vote Parser';
        $this->load->view('header', $data);
        $this->load->view('Parser/index');
    }

    function get_votes() {
        $threadid = $this->input->post('threadid');
		if(is_numeric($threadid)) {
			$vote_posts = $this->Awful->get_votes($threadid); //Array/stdClass
			if(isset($vote_posts['error'])) {
				echo json_encode(Array('error' => 'yes', 'error_text' => 'No days detected in thread.'));
			} else {
				//process results
				$votecounts = Array();
				$currentlyVoting = Array(); // author_id // target, postID
				$currentDay = 0;
				foreach ($vote_posts as $postID => $thisVotePost) {
					if($thisVotePost['day'] > $currentDay) {
						unset($currentlyVoting);
						$currentlyVoting = Array();
						$currentDay = $thisVotePost['day'];
					}
					foreach($thisVotePost['votes_and_unvotes'] as $thisVote) {
						if($thisVote['type'] == 'unvote') {
							if(isset($currentlyVoting[$thisVotePost['author_id']])) { //If it's not set, then ignore the unvote, it is meaningless.
								$votecount[$thisVotePost['day']][$currentlyVoting[$thisVotePost['author_id']]['target']][$postID] = Array('type' => 'unvote', 'active' => 'yes', 'name' => $thisVotePost['author'], 'post' => $postID, 'page' => $thisVotePost['page']);
								$votecount[$thisVotePost['day']][$currentlyVoting[$thisVotePost['author_id']]['target']][$currentlyVoting[$thisVotePost['author_id']]['postID']]['active'] = 'no';
								unset($currentlyVoting[$thisVotePost['author_id']]);
							}
						} else {
							if(!isset($votecount[$thisVotePost['day']]))
								$votecount[$thisVotePost['day']] = Array();
							if(isset($currentlyVoting[$thisVotePost['author_id']])) {
								$votecount[$thisVotePost['day']][$currentlyVoting[$thisVotePost['author_id']]['target']][$currentlyVoting[$thisVotePost['author_id']]['postID']]['active'] = 'no';
								$votecount[$thisVotePost['day']][$currentlyVoting[$thisVotePost['author_id']]['target']][$postID] = Array('type' => 'unvote', 'active' => 'no', 'name' => $thisVotePost['author'], 'post' => $postID, 'page' => $thisVotePost['page']);
								unset($currentlyVoting[$thisVotePost['author_id']]);
							}
							$currentlyVoting[$thisVotePost['author_id']] = Array('target' => $thisVote['target'], 'postID' => $postID);
							$votecount[$thisVotePost['day']][$thisVote['target']][$postID] = Array('type' => 'vote', 'active' => 'yes', 'name' => $thisVotePost['author'], 'post' => $postID, 'page' => $thisVotePost['page']);
						}
						
					}
				}
				echo json_encode(Array('error' => 'no', 'votecount' => $votecount));
			}
		} else {
			echo json_encode(Array('error' => 'yes', 'error_text' => 'Thread ID must be numeric.'));
		}
        
    }



}