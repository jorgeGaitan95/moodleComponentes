<?php
// This file is part of Moodle - http://moodle.org/
//
// Moodle is free software: you can redistribute it and/or modify
// it under the terms of the GNU General Public License as published by
// the Free Software Foundation, either version 3 of the License, or
// (at your option) any later version.
//
// Moodle is distributed in the hope that it will be useful,
// but WITHOUT ANY WARRANTY; without even the implied warranty of
// MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
// GNU General Public License for more details.
//
// You should have received a copy of the GNU General Public License
// along with Moodle.  If not, see <http://www.gnu.org/licenses/>.
/**
 * Defines the version and other meta-info about the plugin
 *
 * Setting the $plugin->version to 0 prevents the plugin from being installed.
 * See https://docs.moodle.org/dev/version.php for more info.
 *
 * @package    local_flowdiagram
 * @copyright  2017 Jorge Gaitán <jorgegaitan903@gmail.com>
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
defined('MOODLE_INTERNAL') || die();
function local_estrategia_didactica_extend_navigation(global_navigation $navigation) {
$nodeFoo = $navigation->add('Estrategia Didáctica');
$nodeBar = $nodeFoo->add('Actividad Formación');
$nodeVaribilidaddes=$nodeBar->add('Variabilidad1',new moodle_url('/local/estrategia_didactica/index.php'));
$nodeVaribilidaddes=$nodeBar->add('Variabilidad2',new moodle_url('/local/estrategia_didactica/variabilidad2.php'));
$nodeVaribilidaddes=$nodeBar->add('PruebaQuiz',new moodle_url('/local/estrategia_didactica/variabilidad2.php'));
}
function getEstrategiaDidactica($userid, $courseid){
  global $DB;
  return $DB->get_record('assigneducational_strategy', array('userid'=>$userid,'courseid'=>$courseid));
}
function getActivities($userid, $courseid){
  global $DB;
  $activities=array();
  $estrategia_didactica = getEstrategiaDidactica($userid,$courseid);
  $result= $DB->get_records('activities', array('educational_strategy_id'=>$estrategia_didactica->educational_strategy_id));
  foreach ($result as $activity) {
    $url= new moodle_url('/local/estrategia_didactica/index.php',array('id' => 3,'activityid'=>$activity->id));
    $aux= array('id' => $activity->id ,'url' =>$url,'name' => $activity->name,'description'=>$activity->description);
    array_push($activities,$aux);
    //print_object($aux);
  }
  return $activities;
}
function getTemplateName($activityid){
  global $DB;
  $template=$DB->get_record_sql('SELECT tmp.templatename FROM {template} as tmp
    INNER JOIN {template_activities} as tmp_act on tmp.id = tmp_act.templateid
    where tmp_act.active=1 and tmp_act.activitiesid=?',array($activityid));
  return $template->templatename;
}

function getComponents($activitiesid){
  global $DB;
  $data=(object)array();
  $components =$DB->get_records_sql('SELECT * FROM {template_activities} as template_act
    INNER JOIN {components} as comp on template_act.id=comp.template_activities_id
    WHERE template_act.activitiesid=?',array($activitiesid));
  foreach ($components as $component) {
    if($component->typecomponents_id==1){
      $video=$DB->get_record('video', array('idcomponent'=>$component->id));
      $data->video=$video;
    }
    if($component->typecomponents_id==2){
      $presentacion=$DB->get_record('viewer', array('idcomponent'=>$component->id));
      $data->presentacion=$presentacion;
    }
    if($component->typecomponents_id==6){
      $repository=listarArchivos('25');
      $data->repository=$repository;
    }
    if($component->typecomponents_id==7){
      $idForum= getForumid($component->id);
      redirect(new moodle_url('/local/estrategia_didactica/forumview.php', array('id' => $idForum)));
    }
    //TODO: completar con los demás elementos
  }
  return $data;
}
function local_estrategia_didactica_pluginfile($course, $context, $filearea, $args, $forcedownload){
  $out = array();

  $fs= get_file_storage();
  $files = $fs->get_area_files($context->id, 'local_estrategia_didactica', 'repository');
  foreach ($files as $file) {
    $filename = $file->get_filename();
    $url = moodle_url::make_pluginfile_url(25,'course','educational',6,'/','a.pdf',false);
    $out[] = html_writer::link($url, $filename);
  }

  $br = html_writer::empty_tag('br');
  return implode($br, $out);
}
function createFile($contextid,$filepath){
  $fs= get_file_storage();
  $fileinfo = array(
    'contextid' => 25,
    'component' => 'course',
    'filearea' => 'educational',
    'itemid' => 6,
    'filepath' => '/',
    'filename' => 'a.pdf');
  $fs->create_file_from_pathname($fileinfo,$filepath);
  print_object($fs);
/*
  //READ FILES

  // Get file
  $file = $fs->get_file($fileinfo['contextid'], $fileinfo['component'], $fileinfo['filearea'],
                        $fileinfo['itemid'], $fileinfo['filepath'], $fileinfo['filename']);

  // Read contents
  if ($file) {
      $contents = $file->get_content();
      print_object($contents);
  } else {
      // file doesn't exist - do something
      echo "el archivo no existe";
  }*/
}
function listarArchivos($contextid){
  $out = array();
  $fs= get_file_storage();
  $files = $fs->get_area_files($contextid, 'local_estrategia_didactica', 'repository');
  foreach ($files as $file) {
    $type=$file->get_mimetype();
    $ulrImg;
    switch ($type) {
      case 'application/pdf':
        $urlImg= new moodle_url('/local/estrategia_didactica/img/pdf-24.png');
        break;
      case 'text/plain':
        $urlImg= new moodle_url('/local/estrategia_didactica/img/text-24.png');
        break;
      default:
        $urlImg='a';
        break;
    }
    $filename = $file->get_filename();
    $url = moodle_url::make_pluginfile_url(25,'course','educational',6,'/','a.pdf',false);
    $s .= html_writer::start_tag('div',array('class' => 'repositoryrow'));
    $s .=html_writer::img($urlImg, 'class');
    $s .= html_writer::link($url, $filename);
    $s .= html_writer::end_tag('div');
  }
  return $s;
}
function getForumid($componentid){
  global $DB;
  $forum_component=$DB->get_record('forum_components',array('idcomponent'=>$componentid));
  return $forum_component->forumid;
}
/**
 * Gets a post with all info ready for forum_print_post
 * Most of these joins are just to get the forum id
 *
 * @global object
 * @global object
 * @param int $postid
 * @return mixed array of posts or false
 */
function get_post_full($postid) {
    global $CFG, $DB;

    $allnames = get_all_user_name_fields(true, 'u');
    return $DB->get_record_sql("SELECT p.*, d.forum, $allnames, u.email, u.picture, u.imagealt
                             FROM {forum_posts} p
                                  JOIN {forum_discussions} d ON p.discussion = d.id
                                  LEFT JOIN {user} u ON p.userid = u.id
                            WHERE p.id = ?", array($postid));
}
function print_discussion($course, $forum, $discussion, $post, $mode, $canreply=NULL, $canrate=false) {
    global $USER, $CFG;

    require_once($CFG->dirroot.'/rating/lib.php');

    $ownpost = (isloggedin() && $USER->id == $post->userid);
    //$modcontext = context_module::instance($cm->id);
    /*if ($canreply === NULL) {
        $reply = forum_user_can_post($forum, $discussion, $USER, $cm, $course, $modcontext);
    } else {*/
      $reply = $canreply;
    //}

    // $cm holds general cache for forum functions
    /*$cm->cache = new stdClass;
    $cm->cache->groups      = groups_get_all_groups($course->id, 0, $cm->groupingid);
    $cm->cache->usersgroups = array();*/

    $posters = array();

    // preload all posts - TODO: improve...
    if ($mode == FORUM_MODE_FLATNEWEST) {
        $sort = "p.created DESC";
    } else {
        $sort = "p.created ASC";
    }

    $forumtracked = tp_is_tracked($forum);
    $posts =get_all_discussion_posts($discussion->id, $sort, $forumtracked);
    $post = $posts[$post->id];
    foreach ($posts as $pid=>$p) {
        $posters[$p->userid] = $p->userid;
    }

    // preload all groups of ppl that posted in this discussion
    /*if ($postersgroups = groups_get_all_groups($course->id, $posters, $cm->groupingid, 'gm.id, gm.groupid, gm.userid')) {
        foreach($postersgroups as $pg) {
            if (!isset($cm->cache->usersgroups[$pg->userid])) {
                $cm->cache->usersgroups[$pg->userid] = array();
            }
            $cm->cache->usersgroups[$pg->userid][$pg->groupid] = $pg->groupid;
        }
        unset($postersgroups);
    }*/

    //load ratings
    if ($forum->assessed != RATING_AGGREGATE_NONE) {
        $ratingoptions = new stdClass;
        $ratingoptions->context = $modcontext;
        $ratingoptions->component = 'mod_forum';
        $ratingoptions->ratingarea = 'post';
        $ratingoptions->items = $posts;
        $ratingoptions->aggregate = $forum->assessed;//the aggregation method
        $ratingoptions->scaleid = $forum->scale;
        $ratingoptions->userid = $USER->id;
        if ($forum->type == 'single' or !$discussion->id) {
            $ratingoptions->returnurl = "$CFG->wwwroot/mod/forum/view.php?id=$cm->id";
        } else {
            $ratingoptions->returnurl = "$CFG->wwwroot/mod/forum/discuss.php?d=$discussion->id";
        }
        $ratingoptions->assesstimestart = $forum->assesstimestart;
        $ratingoptions->assesstimefinish = $forum->assesstimefinish;

        $rm = new rating_manager();
        $posts = $rm->get_ratings($ratingoptions);
    }


    $post->forum = $forum->id;   // Add the forum id to the post object, later used by forum_print_post
    $post->forumtype = $forum->type;

    $post->subject = format_string($post->subject);

    $postread = !empty($post->postread);
    print_post($post, $discussion, $forum,$course, $ownpost, $reply, false,
                         '', '', $postread, true, $forumtracked);

    print_posts_nested($course, $forum, $discussion, $post, $reply, $forumtracked, $posts);
    /*switch ($mode) {
        case FORUM_MODE_FLATOLDEST :
        case FORUM_MODE_FLATNEWEST :
        default:
           print_posts_flat($course, $forum, $discussion, $post, $mode, $reply, $forumtracked, $posts);
           break;

        case FORUM_MODE_THREADED :
           print_posts_threaded($course, $forum, $discussion, $post, 0, $reply, $forumtracked, $posts);
           break;

        case FORUM_MODE_NESTED :
          print_posts_nested($course, $forum, $discussion, $post, $reply, $forumtracked, $posts);
          break;
    }*/
}
/**
 * Tells whether a specific forum is tracked by the user. A user can optionally
 * be specified. If not specified, the current user is assumed.
 *
 * @global object
 * @global object
 * @global object
 * @param mixed $forum If int, the id of the forum being checked; if object, the forum object
 * @param int $userid The id of the user being checked (optional).
 * @return boolean
 */
function tp_is_tracked($forum, $user=false) {
    global $USER, $CFG, $DB;

    if ($user === false) {
        $user = $USER;
    }

    if (isguestuser($user) or empty($user->id)) {
        return false;
    }

    // Work toward always passing an object...
    if (is_numeric($forum)) {
        debugging('Better use proper forum object.', DEBUG_DEVELOPER);
        $forum = $DB->get_record('forum', array('id' => $forum));
    }

    if (!tp_can_track_forums($forum, $user)) {
        return false;
    }

    $forumallows = ($forum->trackingtype == FORUM_TRACKING_OPTIONAL);
    $forumforced = ($forum->trackingtype == FORUM_TRACKING_FORCED);
    $userpref = $DB->get_record('forum_track_prefs', array('userid' => $user->id, 'forumid' => $forum->id));

    if ($CFG->forum_allowforcedreadtracking) {
        return $forumforced || ($forumallows && $userpref === false);
    } else {
        return  ($forumallows || $forumforced) && $userpref === false;
    }
}
/**
 * Determine if a user can track forums and optionally a particular forum.
 * Checks the site settings, the user settings and the forum settings (if
 * requested).
 *
 * @global object
 * @global object
 * @global object
 * @param mixed $forum The forum object to test, or the int id (optional).
 * @param mixed $userid The user object to check for (optional).
 * @return boolean
 */
function tp_can_track_forums($forum=false, $user=false) {
    global $USER, $CFG, $DB;

    // if possible, avoid expensive
    // queries
    if (empty($CFG->forum_trackreadposts)) {
        return false;
    }

    if ($user === false) {
        $user = $USER;
    }

    if (isguestuser($user) or empty($user->id)) {
        return false;
    }

    if ($forum === false) {
        if ($CFG->forum_allowforcedreadtracking) {
            // Since we can force tracking, assume yes without a specific forum.
            return true;
        } else {
            return (bool)$user->trackforums;
        }
    }

    // Work toward always passing an object...
    if (is_numeric($forum)) {
        debugging('Better use proper forum object.', DEBUG_DEVELOPER);
        $forum = $DB->get_record('forum', array('id' => $forum), '', 'id,trackingtype');
    }
    $forumallows = ($forum->trackingtype == FORUM_TRACKING_OPTIONAL);
    $forumforced = ($forum->trackingtype == FORUM_TRACKING_FORCED);

    if ($CFG->forum_allowforcedreadtracking) {
        // If we allow forcing, then forced forums takes procidence over user setting.
        return ($forumforced || ($forumallows  && (!empty($user->trackforums) && (bool)$user->trackforums)));
    } else {
        // If we don't allow forcing, user setting trumps.
        return ($forumforced || $forumallows)  && !empty($user->trackforums);
    }
}
/**
 * Gets all posts in discussion including top parent.
 *
 * @global object
 * @global object
 * @global object
 * @param int $discussionid
 * @param string $sort
 * @param bool $tracking does user track the forum?
 * @return array of posts
 */
function get_all_discussion_posts($discussionid, $sort, $tracking=false) {
  global $CFG, $DB, $USER;

  $tr_sel  = "";
  $tr_join = "";
  $params = array();

  if ($tracking) {
      $tr_sel  = ", fr.id AS postread";
      $tr_join = "LEFT JOIN {forum_read} fr ON (fr.postid = p.id AND fr.userid = ?)";
      $params[] = $USER->id;
  }

  $allnames = get_all_user_name_fields(true, 'u');

  $params[] = $discussionid;
  if (!$posts = $DB->get_records_sql("SELECT p.*, $allnames, u.email, u.picture, u.imagealt $tr_sel
                                   FROM {forum_posts} p
                                        LEFT JOIN {user} u ON p.userid = u.id
                                        $tr_join
                                  WHERE p.discussion = ?
                               ORDER BY $sort", $params)) {
      return array();
  }
  foreach ($posts as $pid=>$p) {
      if ($tracking) {
          if (tp_is_post_old($p)) {
               $posts[$pid]->postread = true;
          }
      }
      if (!$p->parent) {
          continue;
      }
      if (!isset($posts[$p->parent])) {
          continue; // parent does not exist??
      }
      if (!isset($posts[$p->parent]->children)) {
          $posts[$p->parent]->children = array();
      }
      $posts[$p->parent]->children[$pid] =& $posts[$pid];
  }

  // Start with the last child of the first post.
  $post = &$posts[reset($posts)->id];

  $lastpost = false;
  while (!$lastpost) {
      if (!isset($post->children)) {
          $post->lastpost = true;
          $lastpost = true;
      } else {
           // Go to the last child of this post.
          $post = &$posts[end($post->children)->id];
      }
  }
  return $posts;
}
/**
 * @global object
 * @param object $post
 * @param int $time Defautls to time()
 */
function tp_is_post_old($post, $time=null) {
    global $CFG;
    if (is_null($time)) {
        $time = time();
    }
    return ($post->modified < ($time - ($CFG->forum_oldpostdays * 24 * 3600)));
}
/**
 * Print a forum post
 *
 * @global object
 * @global object
 * @uses FORUM_MODE_THREADED
 * @uses PORTFOLIO_FORMAT_PLAINHTML
 * @uses PORTFOLIO_FORMAT_FILE
 * @uses PORTFOLIO_FORMAT_RICHHTML
 * @uses PORTFOLIO_ADD_TEXT_LINK
 * @uses CONTEXT_MODULE
 * @param object $post The post to print.
 * @param object $discussion
 * @param object $forum
 * @param object $cm
 * @param object $course
 * @param boolean $ownpost Whether this post belongs to the current user.
 * @param boolean $reply Whether to print a 'reply' link at the bottom of the message.
 * @param boolean $link Just print a shortened version of the post as a link to the full post.
 * @param string $footer Extra stuff to print after the message.
 * @param string $highlight Space-separated list of terms to highlight.
 * @param int $post_read true, false or -99. If we already know whether this user
 *          has read this post, pass that in, otherwise, pass in -99, and this
 *          function will work it out.
 * @param boolean $dummyifcantsee When forum_user_can_see_post says that
 *          the current user can't see this post, if this argument is true
 *          (the default) then print a dummy 'you can't see this post' post.
 *          If false, don't output anything at all.
 * @param bool|null $istracked
 * @return void
 */
function print_post($post, $discussion, $forum, $course, $ownpost=false, $reply=false, $link=false,
                          $footer="", $highlight="", $postisread=null, $dummyifcantsee=true, $istracked=null, $return=false) {
    global $USER, $CFG, $OUTPUT;

    require_once($CFG->libdir . '/filelib.php');

    // String cache
    static $str;
    // This is an extremely hacky way to ensure we only print the 'unread' anchor
    // the first time we encounter an unread post on a page. Ideally this would
    // be moved into the caller somehow, and be better testable. But at the time
    // of dealing with this bug, this static workaround was the most surgical and
    // it fits together with only printing th unread anchor id once on a given page.
    static $firstunreadanchorprinted = false;

    //$modcontext = context_module::instance($cm->id);

    $post->course = $course->id;
    $post->forum  = $forum->id;
    //$post->message = file_rewrite_pluginfile_urls($post->message, 'pluginfile.php', $modcontext->id, 'mod_forum', 'post', $post->id);
    /*if (!empty($CFG->enableplagiarism)) {
        require_once($CFG->libdir.'/plagiarismlib.php');
        $post->message .= plagiarism_get_links(array('userid' => $post->userid,
            'content' => $post->message,
            'cmid' => $cm->id,
            'course' => $post->course,
            'forum' => $post->forum));
    }*/

    // caching
    /*if (!isset($cm->cache)) {
        $cm->cache = new stdClass;
    }*/

    /*if (!isset($cm->cache->caps)) {
        $cm->cache->caps = array();
        $cm->cache->caps['mod/forum:viewdiscussion']   = has_capability('mod/forum:viewdiscussion', $modcontext);
        $cm->cache->caps['moodle/site:viewfullnames']  = has_capability('moodle/site:viewfullnames', $modcontext);
        $cm->cache->caps['mod/forum:editanypost']      = has_capability('mod/forum:editanypost', $modcontext);
        $cm->cache->caps['mod/forum:splitdiscussions'] = has_capability('mod/forum:splitdiscussions', $modcontext);
        $cm->cache->caps['mod/forum:deleteownpost']    = has_capability('mod/forum:deleteownpost', $modcontext);
        $cm->cache->caps['mod/forum:deleteanypost']    = has_capability('mod/forum:deleteanypost', $modcontext);
        $cm->cache->caps['mod/forum:viewanyrating']    = has_capability('mod/forum:viewanyrating', $modcontext);
        $cm->cache->caps['mod/forum:exportpost']       = has_capability('mod/forum:exportpost', $modcontext);
        $cm->cache->caps['mod/forum:exportownpost']    = has_capability('mod/forum:exportownpost', $modcontext);
    }*/

    /*if (!isset($cm->uservisible)) {
        $cm->uservisible = \core_availability\info_module::is_user_visible($cm, 0, false);
    }*/

    if ($istracked && is_null($postisread)) {
        $postisread = tp_is_post_read($USER->id, $post);
      }

  //SE ASUME QUE LOS USUARIO PUEDEN VER LOS POST
      /*if (!forum_user_can_see_post($forum, $discussion, $post, NULL, $cm)) {
          $output = '';
          if (!$dummyifcantsee) {
              if ($return) {
                  return $output;
              }
              echo $output;
              return;
          }
          $output .= html_writer::tag('a', '', array('id'=>'p'.$post->id));
          $output .= html_writer::start_tag('div', array('class'=>'forumpost clearfix',
                                                         'role' => 'region',
                                                         'aria-label' => get_string('hiddenforumpost', 'forum')));
          $output .= html_writer::start_tag('div', array('class'=>'row header'));
          $output .= html_writer::tag('div', '', array('class'=>'left picture')); // Picture
          if ($post->parent) {
              $output .= html_writer::start_tag('div', array('class'=>'topic'));
          } else {
              $output .= html_writer::start_tag('div', array('class'=>'topic starter'));
          }
          $output .= html_writer::tag('div', get_string('forumsubjecthidden','forum'), array('class' => 'subject',
                                                                                             'role' => 'header')); // Subject.
          $output .= html_writer::tag('div', get_string('forumauthorhidden', 'forum'), array('class' => 'author',
                                                                                             'role' => 'header')); // Author.
          $output .= html_writer::end_tag('div');
          $output .= html_writer::end_tag('div'); // row
          $output .= html_writer::start_tag('div', array('class'=>'row'));
          $output .= html_writer::tag('div', '&nbsp;', array('class'=>'left side')); // Groups
          $output .= html_writer::tag('div', get_string('forumbodyhidden','forum'), array('class'=>'content')); // Content
          $output .= html_writer::end_tag('div'); // row
          $output .= html_writer::end_tag('div'); // forumpost

          if ($return) {
              return $output;
          }
          echo $output;
          return;
      }*/

      if (empty($str)) {
          $str = new stdClass;
          $str->edit         = get_string('edit', 'forum');
          $str->delete       = get_string('delete', 'forum');
          $str->reply        = get_string('reply', 'forum');
          $str->parent       = get_string('parent', 'forum');
          $str->pruneheading = get_string('pruneheading', 'forum');
          $str->prune        = get_string('prune', 'forum');
          $str->displaymode     = get_user_preferences('forum_displaymode', $CFG->forum_displaymode);
          $str->markread     = get_string('markread', 'forum');
          $str->markunread   = get_string('markunread', 'forum');
      }

      $discussionlink = new moodle_url('/mod/forum/discuss.php', array('d'=>$post->discussion));
      // Build an object that represents the posting user
      $postuser = new stdClass;
      $postuserfields = explode(',', user_picture::fields());
      $postuser = username_load_fields_from_object($postuser, $post, null, $postuserfields);
      $postuser->id = $post->userid;
      $postuser->fullname    = fullname($postuser, $cm->cache->caps['moodle/site:viewfullnames']);
      $postuser->profilelink = new moodle_url('/user/view.php', array('id'=>$post->userid, 'course'=>$course->id));
      $postuser->profilelink = new moodle_url('/user/view.php', array('id'=>$post->userid, 'course'=>$course->id));

      // Prepare the groups the posting user belongs to
      /*if (isset($cm->cache->usersgroups)) {
          $groups = array();
          if (isset($cm->cache->usersgroups[$post->userid])) {
              foreach ($cm->cache->usersgroups[$post->userid] as $gid) {
                  $groups[$gid] = $cm->cache->groups[$gid];
              }
          }
      } else {
          $groups = groups_get_all_groups($course->id, $post->userid, $cm->groupingid);
      }*/

      // Prepare the attachements for the post, files then images
      //list($attachments, $attachedimages) = forum_print_attachments($post, $cm, 'separateimages');

      // Determine if we need to shorten this post
      $shortenpost = ($link && (strlen(strip_tags($post->message)) > $CFG->forum_longpost));


      // Prepare an array of commands
      $commands = array();

      // Add a permalink.
      $permalink = new moodle_url($discussionlink);
      $permalink->set_anchor('p' . $post->id);
      $commands[] = array('url' => $permalink, 'text' => get_string('permalink', 'forum'));

      // SPECIAL CASE: The front page can display a news item post to non-logged in users.
      // Don't display the mark read / unread controls in this case.
      if ($istracked && $CFG->forum_usermarksread && isloggedin()) {
          $url = new moodle_url($discussionlink, array('postid'=>$post->id, 'mark'=>'unread'));
          $text = $str->markunread;
          if (!$postisread) {
              $url->param('mark', 'read');
              $text = $str->markread;
          }
          if ($str->displaymode == FORUM_MODE_THREADED) {
              $url->param('parent', $post->parent);
          } else {
              $url->set_anchor('p'.$post->id);
          }
          $commands[] = array('url'=>$url, 'text'=>$text);
      }

      // Zoom in to the parent specifically
      if ($post->parent) {
          $url = new moodle_url($discussionlink);
          if ($str->displaymode == FORUM_MODE_THREADED) {
              $url->param('parent', $post->parent);
          } else {
              $url->set_anchor('p'.$post->parent);
          }
          $commands[] = array('url'=>$url, 'text'=>$str->parent);
      }

      // Hack for allow to edit news posts those are not displayed yet until they are displayed
      $age = time() - $post->created;
      if (!$post->parent && $forum->type == 'news' && $discussion->timestart > time()) {
          $age = 0;
      }
  /*
      if ($forum->type == 'single' and $discussion->firstpost == $post->id) {
          if (has_capability('moodle/course:manageactivities', $modcontext)) {
              // The first post in single simple is the forum description.
              $commands[] = array('url'=>new moodle_url('/course/modedit.php', array('update'=>$cm->id, 'sesskey'=>sesskey(), 'return'=>1)), 'text'=>$str->edit);
          }
      } else if (($ownpost && $age < $CFG->maxeditingtime) || $cm->cache->caps['mod/forum:editanypost']) {
          $commands[] = array('url'=>new moodle_url('/mod/forum/post.php', array('edit'=>$post->id)), 'text'=>$str->edit);
      }

      if ($cm->cache->caps['mod/forum:splitdiscussions'] && $post->parent && $forum->type != 'single') {
          $commands[] = array('url'=>new moodle_url('/mod/forum/post.php', array('prune'=>$post->id)), 'text'=>$str->prune, 'title'=>$str->pruneheading);
      }

      if ($forum->type == 'single' and $discussion->firstpost == $post->id) {
          // Do not allow deleting of first post in single simple type.
      } else if (($ownpost && $age < $CFG->maxeditingtime && $cm->cache->caps['mod/forum:deleteownpost']) || $cm->cache->caps['mod/forum:deleteanypost']) {
          $commands[] = array('url'=>new moodle_url('/mod/forum/post.php', array('delete'=>$post->id)), 'text'=>$str->delete);
      }
  */
      if ($reply) {
          $commands[] = array('url'=>new moodle_url('/mod/forum/post.php#mformforum', array('reply'=>$post->id)), 'text'=>$str->reply);
      }
  /*
      if ($CFG->enableportfolios && ($cm->cache->caps['mod/forum:exportpost'] || ($ownpost && $cm->cache->caps['mod/forum:exportownpost']))) {
          $p = array('postid' => $post->id);
          require_once($CFG->libdir.'/portfoliolib.php');
          $button = new portfolio_add_button();
          $button->set_callback_options('forum_portfolio_caller', array('postid' => $post->id), 'mod_forum');
          if (empty($attachments)) {
              $button->set_formats(PORTFOLIO_FORMAT_PLAINHTML);
          } else {
              $button->set_formats(PORTFOLIO_FORMAT_RICHHTML);
          }

          $porfoliohtml = $button->to_html(PORTFOLIO_ADD_TEXT_LINK);
          if (!empty($porfoliohtml)) {
              $commands[] = $porfoliohtml;
          }
      }
      // Finished building commands
  */

      // Begin output

      $output  = '';

      if ($istracked) {
          if ($postisread) {
              $forumpostclass = ' read';
          } else {
              $forumpostclass = ' unread';
              // If this is the first unread post printed then give it an anchor and id of unread.
              if (!$firstunreadanchorprinted) {
                  $output .= html_writer::tag('a', '', array('id' => 'unread'));
                  $firstunreadanchorprinted = true;
              }
          }
      } else {
          // ignore trackign status if not tracked or tracked param missing
          $forumpostclass = '';
      }

      $topicclass = '';
      if (empty($post->parent)) {
          $topicclass = ' firstpost starter';
      }

      if (!empty($post->lastpost)) {
          $forumpostclass .= ' lastpost';
      }

      // Flag to indicate whether we should hide the author or not.
      //$authorhidden = is_author_hidden($post, $forum);
      $authorhidden = false;
      $postbyuser = new stdClass;
      $postbyuser->post = $post->subject;
      $postbyuser->user = $postuser->fullname;
      $discussionbyuser = get_string('postbyuser', 'forum', $postbyuser);
      $output .= html_writer::tag('a', '', array('id'=>'p'.$post->id));
      // Begin forum post.
      $output .= html_writer::start_div('forumpost clearfix' . $forumpostclass . $topicclass,
          ['role' => 'region', 'aria-label' => $discussionbyuser]);
      // Begin header row.
      $output .= html_writer::start_div('row header clearfix');

      // User picture.
      if (!$authorhidden) {
          $picture = $OUTPUT->user_picture($postuser, ['courseid' => $course->id]);
          $output .= html_writer::div($picture, 'left picture');
          $topicclass = 'topic' . $topicclass;
      }

      // Begin topic column.
      $output .= html_writer::start_div($topicclass);
      $postsubject = $post->subject;
      if (empty($post->subjectnoformat)) {
          $postsubject = format_string($postsubject);
      }
      $output .= html_writer::div($postsubject, 'subject', ['role' => 'heading', 'aria-level' => '2']);

      if ($authorhidden) {
          $bytext = userdate($post->modified);
      } else {
          $by = new stdClass();
          $by->date = userdate($post->modified);
          $by->name = html_writer::link($postuser->profilelink, $postuser->fullname);
          $bytext = get_string('bynameondate', 'forum', $by);
      }
      $bytextoptions = [
          'role' => 'heading',
          'aria-level' => '2',
      ];
      $output .= html_writer::div($bytext, 'author', $bytextoptions);
      // End topic column.
      $output .= html_writer::end_div();

      // End header row.
      $output .= html_writer::end_div();

      // Row with the forum post content.
      $output .= html_writer::start_div('row maincontent clearfix');
      // Show if author is not hidden or we have groups.
  /*if (!$authorhidden || $groups) {
          $output .= html_writer::start_div('left');
          $groupoutput = '';
          if ($groups) {
              $groupoutput = print_group_picture($groups, $course->id, false, true, true);
          }
          if (empty($groupoutput)) {
              $groupoutput = '&nbsp;';
          }
          $output .= html_writer::div($groupoutput, 'grouppictures');
          $output .= html_writer::end_div(); // Left side.
      }
  */
      $output .= html_writer::start_tag('div', array('class'=>'no-overflow'));
      $output .= html_writer::start_tag('div', array('class'=>'content'));

      $options = new stdClass;
      $options->para    = false;
      $options->trusted = $post->messagetrust;
      $options->context = $modcontext;
      if ($shortenpost) {
          // Prepare shortened version by filtering the text then shortening it.
          $postclass    = 'shortenedpost';
          $postcontent  = format_text($post->message, $post->messageformat, $options);
          $postcontent  = shorten_text($postcontent, $CFG->forum_shortpost);
          $postcontent .= html_writer::link($discussionlink, get_string('readtherest', 'forum'));
          $postcontent .= html_writer::tag('div', '('.get_string('numwords', 'moodle', count_words($post->message)).')',
              array('class'=>'post-word-count'));
      } else {
          // Prepare whole post
          $postclass    = 'fullpost';
          $postcontent  = format_text($post->message, $post->messageformat, $options, $course->id);
          if (!empty($highlight)) {
              $postcontent = highlight($highlight, $postcontent);
          }
          if (!empty($forum->displaywordcount)) {
              $postcontent .= html_writer::tag('div', get_string('numwords', 'moodle', count_words($post->message)),
                  array('class'=>'post-word-count'));
          }
          $postcontent .= html_writer::tag('div', $attachedimages, array('class'=>'attachedimages'));
      }

      // Output the post content
      $output .= html_writer::tag('div', $postcontent, array('class'=>'posting '.$postclass));
      $output .= html_writer::end_tag('div'); // Content
      $output .= html_writer::end_tag('div'); // Content mask
      $output .= html_writer::end_tag('div'); // Row

      $output .= html_writer::start_tag('div', array('class'=>'row side'));
      $output .= html_writer::tag('div','&nbsp;', array('class'=>'left'));
      $output .= html_writer::start_tag('div', array('class'=>'options clearfix'));

      if (!empty($attachments)) {
          $output .= html_writer::tag('div', $attachments, array('class' => 'attachments'));
      }

      // Output ratings
      if (!empty($post->rating)) {
          $output .= html_writer::tag('div', $OUTPUT->render($post->rating), array('class'=>'forum-post-rating'));
      }

      // Output the commands
      $commandhtml = array();
      foreach ($commands as $command) {
          if (is_array($command)) {
              $commandhtml[] = html_writer::link($command['url'], $command['text']);
          } else {
              $commandhtml[] = $command;
          }
      }
      $output .= html_writer::tag('div', implode(' | ', $commandhtml), array('class'=>'commands'));

      // Output link to post if required
      if ($link) {
          /*if (forum_user_can_post($forum, $discussion, $USER, $cm, $course, $modcontext)) {
              $langstring = 'discussthistopic';
          } else {
              $langstring = 'viewthediscussion';
          }*/
          $langstring = 'discussthistopic';
          if ($post->replies == 1) {
              $replystring = get_string('repliesone', 'forum', $post->replies);
          } else {
              $replystring = get_string('repliesmany', 'forum', $post->replies);
          }
          if (!empty($discussion->unread) && $discussion->unread !== '-') {
              $replystring .= ' <span class="sep">/</span> <span class="unread">';
              if ($discussion->unread == 1) {
                  $replystring .= get_string('unreadpostsone', 'forum');
              } else {
                  $replystring .= get_string('unreadpostsnumber', 'forum', $discussion->unread);
              }
              $replystring .= '</span>';
          }

          $output .= html_writer::start_tag('div', array('class'=>'link'));
          $output .= html_writer::link($discussionlink, get_string($langstring, 'forum'));
          $output .= '&nbsp;('.$replystring.')';
          $output .= html_writer::end_tag('div'); // link
      }

      // Output footer if required
      if ($footer) {
          $output .= html_writer::tag('div', $footer, array('class'=>'footer'));
      }

      // Close remaining open divs
      $output .= html_writer::end_tag('div'); // content
      $output .= html_writer::end_tag('div'); // row
      $output .= html_writer::end_tag('div'); // forumpost

      // Mark the forum post as read if required
      /*if ($istracked && !$CFG->forum_usermarksread && !$postisread) {
          forum_tp_mark_post_read($USER->id, $post, $forum->id);
      }*/

      if ($return) {
          return $output;
      }
      echo $output;
      return;

}
/**
 * @global object
 * @param int $userid
 * @param object $post
 */
function tp_is_post_read($userid, $post) {
    global $DB;
    return (tp_is_post_old($post) ||
            $DB->record_exists('forum_read', array('userid' => $userid, 'postid' => $post->id)));
}
/**
 * Checks whether the author's name and picture for a given post should be hidden or not.
 *
 * @param object $post The forum post.
 * @param object $forum The forum object.
 * @return bool
 * @throws coding_exception
 */
function is_author_hidden($post, $forum) {
    if (!isset($post->parent)) {
        throw new coding_exception('$post->parent must be set.');
    }
    if (!isset($forum->type)) {
        throw new coding_exception('$forum->type must be set.');
    }
    if ($forum->type === 'single' && empty($post->parent)) {
        return true;
    }
    return false;
}
/**
 * @global object
 * @global object
 * @uses FORUM_MODE_FLATNEWEST
 * @param object $course
 * @param object $cm
 * @param object $forum
 * @param object $discussion
 * @param object $post
 * @param object $mode
 * @param bool $reply
 * @param bool $forumtracked
 * @param array $posts
 * @return void
 */
function print_posts_flat($course, $forum, $discussion, $post, $mode, $reply, $forumtracked, $posts) {
    global $USER, $CFG;

    $link  = false;

    foreach ($posts as $post) {
        if (!$post->parent) {
            continue;
        }
        $post->subject = format_string($post->subject);
        $ownpost = ($USER->id == $post->userid);

        $postread = !empty($post->postread);

        print_post($post, $discussion, $forum, $course, $ownpost, $reply, $link,
                             '', '', $postread, true, $forumtracked);
    }
}
/**
 * @todo Document this function
 *
 * @global object
 * @global object
 * @uses CONTEXT_MODULE
 * @return void
 */
function print_posts_threaded($course, $forum, $discussion, $parent, $depth, $reply, $forumtracked, $posts) {
    global $USER, $CFG;

    $link  = false;

    if (!empty($posts[$parent->id]->children)) {
        $posts = $posts[$parent->id]->children;
/*
        $modcontext       = context_module::instance($cm->id);
        $canviewfullnames = has_capability('moodle/site:viewfullnames', $modcontext);
*/      $canviewfullnames = true;
        foreach ($posts as $post) {

            echo '<div class="indent">';
            if ($depth > 0) {
                $ownpost = ($USER->id == $post->userid);
                $post->subject = format_string($post->subject);

                $postread = !empty($post->postread);

                print_post($post, $discussion, $forum, $course, $ownpost, $reply, $link,
                                     '', '', $postread, true, $forumtracked);
            } else {
                /*if (!forum_user_can_see_post($forum, $discussion, $post, NULL, $cm)) {
                    echo "</div>\n";
                    continue;
                }*/
                $by = new stdClass();
                $by->name = fullname($post, $canviewfullnames);
                $by->date = userdate($post->modified);

                if ($forumtracked) {
                    if (!empty($post->postread)) {
                        $style = '<span class="forumthread read">';
                    } else {
                        $style = '<span class="forumthread unread">';
                    }
                } else {
                    $style = '<span class="forumthread">';
                }
                echo $style."<a name=\"$post->id\"></a>".
                     "<a href=\"discuss.php?d=$post->discussion&amp;parent=$post->id\">".format_string($post->subject,true)."</a> ";
                print_string("bynameondate", "forum", $by);
                echo "</span>";
            }

            print_posts_threaded($course, $forum, $discussion, $post, $depth-1, $reply, $forumtracked, $posts);
            echo "</div>\n";
        }
    }
}
/**
 * @todo Document this function
 * @global object
 * @global object
 * @return void
 */
function print_posts_nested($course, $forum, $discussion, $parent, $reply, $forumtracked, $posts) {
    global $USER, $CFG;

    $link  = false;

    if (!empty($posts[$parent->id]->children)) {
        $posts = $posts[$parent->id]->children;

        foreach ($posts as $post) {

            echo '<div class="indent">';
            if (!isloggedin()) {
                $ownpost = false;
            } else {
                $ownpost = ($USER->id == $post->userid);
            }

            $post->subject = format_string($post->subject);
            $postread = !empty($post->postread);

            print_post($post, $discussion, $forum, $course, $ownpost, $reply, $link,
                                 '', '', $postread, true, $forumtracked);
            print_posts_nested($course, $forum, $discussion, $post, $reply, $forumtracked, $posts);
            echo "</div>\n";
        }
    }
}
