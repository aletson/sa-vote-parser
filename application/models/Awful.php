<?php

if (!defined('BASEPATH'))
    exit('No direct script access allowed');

class Awful extends CI_Model
{
    protected $url = 'http://forums.somethingawful.com/showthread.php';
	private $sa_user = '';
	private $sa_pass = '';
    public function __construct()
    {
        //$this->load->database(); We dont need this in this model.

    }
	
    public function get_votes($threadid)
    {
		$page = 1;
        $data = $this->fetch_page($threadid, $page);
        $html = new DOMDocument;
		libxml_use_internal_errors(true);
        $html->loadHTML($data);
        $xpath = new DOMXPath($html);
		$xpath_nextpage = $xpath->query('//a[@title="Next page"]'); //does the next page button exist
		$process_page = 1;
		while(($process_page != 0 || $page == 1) && $page < 150) {
			$xpath_query = $xpath->query('//div[@id="thread"]/table[contains(@class, "post")]');
			$post_num = 0;
			foreach($xpath_query as $post) {
				$post_num++;
				$post_id = $post->getAttribute('id');
				$post_id = str_replace('post', '', $post_id);
				$user_data = $xpath->query('descendant::td[contains(@class, "userinfo")]', $post);
				foreach($user_data as $single_user_data) { //There's only one but it returns the wrong type
					$user_id = $single_user_data->getAttribute('class');
				}
				$user_id = str_replace('userinfo userid-', '', $user_id);
				if($page == 1 && $post_num == 1) {
					$op_uid = $user_id;
				}
				$user_name_dt = $xpath->query('descendant::dt[contains(@class, "author")]', $single_user_data);
				foreach($user_name_dt as $single_user_name) {
					$user_name = $single_user_name->nodeValue;
				}
				$post_body = $xpath->query('descendant::td[contains(@class, "postbody")]', $post);
				foreach($post_body as $single_post_text) {
					$bbc_blocks = $xpath->query('descendant::div[@class="bbc-block"]', $single_post_text); //Find all quotes
					if($bbc_blocks->length) {
					foreach($bbc_blocks as $thisBBCBlock) {
						try {
							$single_post_text->removeChild($thisBBCBlock); //Remove quotes from parser
						} catch(DOMException $e) {
							//HA!
						}
					}
					}
					$post_text = $html->saveHTML($single_post_text); //HTML-only
				}
				if(stripos($post_text, '<b>##vote') > -1 || stripos($post_text, '<b>##unvote') > -1) {
					$vote_posts[$post_id]['author'] = $user_name;
					$vote_posts[$post_id]['author_id'] = $user_id;
					$vote_posts[$post_id]['post_text'] = $post_text;
					$vote_posts[$post_id]['page'] = $page;
				}
				
				if($user_id == $op_uid && preg_match('/<b>[Dd][Aa][Yy] ([1-9]*) [Bb][Ee][Gg][Ii][Nn][Ss] [Nn][Oo][Ww]<\/b>/', $post_text, $day_key) == 1) { //If the OP posted a day
					$days[$day_key[1]] = $post_id;
				}
			}
			$page++;
			if($xpath_nextpage->length) {
				$data = $this->fetch_page($threadid, $page);
				$html = new DOMDocument;
				libxml_use_internal_errors(true);
				$html->loadHTML($data);
				$xpath = new DOMXPath($html);
				$xpath_nextpage = $xpath->query('//a[@title="Next page"]');
			} else {
				$process_page = 0;
			}
		}
		if($page > 150) {
			//Thread too long! Stop parsing, return an error.
		}
		
		
		if(isset($days)) {
			$error = Array();
			$error['error'] = 'yes';
			$error['reason'] = 'No days detected!';
		}
		
		$day_idx = 1; //All games have to start on Day 1 for voting!
		foreach($vote_posts as $postID => $thisVotePost) { //Parse all votes/unvotes, and export to sub-array.
			while(isset($days[$day_idx]) && $days[$day_idx] < $postID) {
				$day_idx++;
			}
			$vote_posts[$postID]['day'] = $day_idx - 1;
			preg_match_all('/<b>##(.*?)<\/b>/', $thisVotePost['post_text'], $votesUnvotesPerPost);
				if(isset($votesUnvotesPerPost[0])) {
					foreach($votesUnvotesPerPost[0] as $key => $thisVote) {
						if(stripos($thisVote, '##vote') > -1) {
							$vote_posts[$postID]['votes_and_unvotes'][$key]['type'] = 'vote';
							preg_match('/(?:##[Vv][Oo][Tt][Ee][:]?[\s]?)(.*?)</', $thisVote, $matched_target);
							$vote_posts[$postID]['votes_and_unvotes'][$key]['target'] = $matched_target[1];
						} else {
							$vote_posts[$postID]['votes_and_unvotes'][$key]['type'] = 'unvote';
						}
					}
				} else {
					$vote_posts[$postID]['error'] = 'thisVote not set!';
					$vote_posts[$postID]['thisVote'] = $thisVoteArr;
				}
			if(!isset($vote_posts[$postID]['votes_and_unvotes'])) { //This is an edge case if an unvote and vote are in the same <b> block. Guess what? It doesn't count!
				unset($vote_posts[$postID]);
			} else {
				unset($vote_posts[$postID]['post_text']); //We don't care.
			}
			
		}
		return $vote_posts;
    }

    public function fetch_page($threadid, $page) {
		//we have to simulate a login first
        $curl_request_url = $this->url . '?threadid=' . $threadid . '&pagenumber=' . $page;
        
        $ch = curl_init();

		//Log in
		curl_setopt($ch, CURLOPT_URL, 'http://forums.somethingawful.com/account.php');
		curl_setopt($ch, CURLOPT_POST, true);
		
		//TODO: voteparser account :10bux:
		curl_setopt($ch, CURLOPT_POSTFIELDS, 'action=login&username=' . urlencode($this->sa_user) . '&password=' . urlencode($this->sa_pass) . '&next=' . urlencode('/'));
		curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
		curl_setopt($ch, CURLOPT_COOKIESESSION, true);
		curl_setopt($ch, CURLOPT_FOLLOWLOCATION, 1);
		curl_setopt($ch, CURLOPT_COOKIEJAR, 'sa-cookie');
		
		$answer = curl_exec($ch);
		if(curl_error($ch)) {
			return curl_error($ch);
		}
		
        curl_setopt($ch, CURLOPT_URL, $curl_request_url);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, '1');
        $result = curl_exec($ch);
        curl_close($ch);
        return $result;
    }


}