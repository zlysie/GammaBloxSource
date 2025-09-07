<?php

	require_once "user.php";

	class Post {
		public $id;
		public Forum $forum;
		public Post $thread;
		public bool $isThreader;
		public Post $replyingPost;
		public User $poster;
		public string $title;
		public string $content;
		public DateTime $postDate;

		public static function FromID($post_id): Post|null {
			include $_SERVER["DOCUMENT_ROOT"]."/core/connection.php";
			$stmt_getuser = $con->prepare("SELECT * FROM `forum_posts` WHERE `post_id` = ?");
			$stmt_getuser->bind_param('i', $group_id);
			$stmt_getuser->execute();
			$result = $stmt_getuser->get_result();

			if($result->num_rows == 1) {
				return new self($result->fetch_assoc());
			} else {
				return null;
			}
		}

		function __construct($rowdata) {
			$this->id = intval($rowdata['post_id']);
			if($this->id != intval($rowdata['post_thread'])) {
				$this->thread = Post::FromID(intval($rowdata['post_thread']));
			} else {
				if($rowdata['post_thread'] == null) {
					$this->isThreader = true;
				}
			}
			$this->forum = Forum::FromID($rowdata['post_forum']);
			$this->replyingPost = Post::FromID(intval($rowdata['post_replyto']));
			$this->poster = User::FromID($rowdata['post_creator']);
			$this->title = str_replace("<", "&lt;", str_replace(">", "&gt;", $rowdata['post_title']));
			$this->content = str_replace("<", "&lt;", str_replace(">", "&gt;", $rowdata['post_content']));
			$this->postDate = DateTime::createFromFormat("Y-m-d H:i:s", $rowdata['post_date']);
		}
	}

	class Forum {
		public $id;
		public ForumGroup $group;
		public $name;
		public $description;

		public static function FromID($group_id): Forum|null {
			include $_SERVER["DOCUMENT_ROOT"]."/core/connection.php";
			$stmt_getuser = $con->prepare("SELECT * FROM `forums` WHERE `forum_id` = ?");
			$stmt_getuser->bind_param('i', $group_id);
			$stmt_getuser->execute();
			$result = $stmt_getuser->get_result();

			if($result->num_rows == 1) {
				return new self($result->fetch_assoc());
			} else {
				return null;
			}
		}

		public function __construct($rowdata) {
			$this->id = intval($rowdata['forum_id']);
			$this->group = ForumGroup::FromID($rowdata['forum_groupid']);
			$this->name = $rowdata['forum_name'];
			$this->description = $rowdata['forum_description'];
		}

		function GetThreadsCount(): int {
			include $_SERVER["DOCUMENT_ROOT"]."/core/connection.php";
			$stmt_getuser = $con->prepare("SELECT * FROM `forum_posts` WHERE `post_forumid` = ? AND `post_thread` IS NULL;");
			$stmt_getuser->bind_param('i', $this->id);
			$stmt_getuser->execute();
			return $stmt_getuser->get_result()->num_rows;
		}

		function GetPostsCount(): int {
			include $_SERVER["DOCUMENT_ROOT"]."/core/connection.php";
			$stmt_getuser = $con->prepare("SELECT * FROM `forum_posts` WHERE `post_forumid` = ?;");
			$stmt_getuser->bind_param('i', $this->id);
			$stmt_getuser->execute();
			return $stmt_getuser->get_result()->num_rows;
		}

		function GetLatestPost(): Post|null {
			include $_SERVER["DOCUMENT_ROOT"]."/core/connection.php";
			$stmt_getuser = $con->prepare("SELECT * FROM `forum_posts` WHERE `post_forumid` = ? ORDER BY `post_date` DESC;");
			$stmt_getuser->bind_param('i', $this->id);
			$stmt_getuser->execute();
			$res = $stmt_getuser->get_result();
			if($res->num_rows != 0) {
				return new Post($res->fetch_assoc());
			} else {
				return null;
			}
		}
	}

	class ForumGroup {
		public $id;
		public $name;

		public static function FromID($group_id): ForumGroup|null {
			include $_SERVER["DOCUMENT_ROOT"]."/core/connection.php";
			$stmt_getuser = $con->prepare("SELECT * FROM `forum_groups` WHERE `group_id` = ?");
			$stmt_getuser->bind_param('i', $group_id);
			$stmt_getuser->execute();
			$result = $stmt_getuser->get_result();

			if($result->num_rows == 1) {
				return new self($result->fetch_assoc());
			} else {
				return null;
			}
		}

		public static function GetAll() {
			include $_SERVER["DOCUMENT_ROOT"]."/core/connection.php";
			$stmt_getgroups = $con->prepare("SELECT * FROM `forum_groups` WHERE 1;");
			$stmt_getgroups->execute();
			$result = $stmt_getgroups->get_result();
			$array = [];

			while(($group = $result->fetch_assoc()) != null) {
				array_push($array, new ForumGroup($group));
			}

			return $array;
		}

		public function __construct($rowdata) {
			$this->id = intval($rowdata['group_id']);
			$this->name = $rowdata['group_name'];
		}

		function GetForums(): array {
			include $_SERVER["DOCUMENT_ROOT"]."/core/connection.php";
			$stmt_getuser = $con->prepare("SELECT * FROM `forums` WHERE `forum_groupid` = ?");
			$stmt_getuser->bind_param('i', $this->id);
			$stmt_getuser->execute();
			$result = $stmt_getuser->get_result();
			$arrayaa = [];
			while(($forum = $result->fetch_assoc()) != null) {
				array_push($arrayaa, new Forum($forum));
			}

			return $arrayaa;
		}
	}