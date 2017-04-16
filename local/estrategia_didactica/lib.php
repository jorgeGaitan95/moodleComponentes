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
    if($component->typecomponents_id==4){
      $idAssign= getAssignid($component->id);
      redirect(new moodle_url('/local/estrategia_didactica/assingview.php', array('id' =>$idAssign)));
    }
    if($component->typecomponents_id==3){
      $idchat= getChatid($component->id);
      redirect(new moodle_url('/local/estrategia_didactica/chatview.php', array('id' =>$idchat)));
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
    switch ($mode) {
        case FORUM_MODE_FLATOLDEST :
        case FORUM_MODE_FLATNEWEST :
           print_posts_flat($course, $forum, $discussion, $post, $mode, $reply, $forumtracked, $posts);
           break;

        case FORUM_MODE_THREADED :
           print_posts_threaded($course, $forum, $discussion, $post, 0, $reply, $forumtracked, $posts);
           break;

        case FORUM_MODE_NESTED :
          print_posts_nested($course, $forum, $discussion, $post, $reply, $forumtracked, $posts);
          break;
    }
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
          $commands[] = array('url'=>new moodle_url('/local/estrategia_didactica/post.php#mformforum', array('reply'=>$post->id)), 'text'=>$str->reply);
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
/**
 * Returns array of forum layout modes
 *
 * @return array
 */
function get_layout_modes() {
    return array (FORUM_MODE_FLATOLDEST => get_string('modeflatoldestfirst', 'forum'),
                  FORUM_MODE_FLATNEWEST => get_string('modeflatnewestfirst', 'forum'),
                  FORUM_MODE_THREADED   => get_string('modethreaded', 'forum'),
                  FORUM_MODE_NESTED     => get_string('modenested', 'forum'));
}
/**
 * Print the drop down that allows the user to select how they want to have
 * the discussion displayed.
 *
 * @param int $id forum id if $forumtype is 'single',
 *              discussion id for any other forum type
 * @param mixed $mode forum layout mode
 * @param string $forumtype optional
 */
function print_mode_form($id, $mode, $forumtype='') {
    global $OUTPUT;
    if ($forumtype == 'single') {
        $select = new single_select(new moodle_url("/local/estrategia_didactica/forumview.php", array('id'=>$id)), 'mode', get_layout_modes(), $mode, null, "mode");
        $select->set_label(get_string('displaymode', 'forum'), array('class' => 'accesshide'));
        $select->class = "forummode";
    }
    echo $OUTPUT->render($select);
}


// POSTS

function get_post_full_forum($postid) {
    global $CFG, $DB;

    $allnames = get_all_user_name_fields(true, 'u');
    return $DB->get_record_sql("SELECT p.*, d.forum, $allnames, u.email, u.picture, u.imagealt
                             FROM {forum_posts} p
                                  JOIN {forum_discussions} d ON p.discussion = d.id
                                  LEFT JOIN {user} u ON p.userid = u.id
                            WHERE p.id = ?", array($postid));
}

/**
 * This function checks whether the user can reply to posts in a forum
 * discussion. Use forum_user_can_post_discussion() to check whether the user
 * can start discussions.
 *
 * @global object
 * @global object
 * @uses DEBUG_DEVELOPER
 * @uses CONTEXT_MODULE
 * @uses VISIBLEGROUPS
 * @param object $forum forum object
 * @param object $discussion
 * @param object $user
 * @param object $cm
 * @param object $course
 * @param object $context
 * @return bool
 */
function user_can_post($forum, $discussion, $user=NULL, $course=NULL, $context=NULL) {
    global $USER, $DB;
    if (empty($user)) {
        $user = $USER;
    }

    // shortcut - guest and not-logged-in users can not post
    if (isguestuser($user) or empty($user->id)) {
        return false;
    }

    if (!isset($discussion->groupid)) {
        debugging('incorrect discussion parameter', DEBUG_DEVELOPER);
        return false;
    }

    /*if (!$cm) {
        debugging('missing cm', DEBUG_DEVELOPER);
        if (!$cm = get_coursemodule_from_instance('forum', $forum->id, $forum->course)) {
            print_error('invalidcoursemodule');
        }
    }*/

    if (!$course) {
        debugging('missing course', DEBUG_DEVELOPER);
        if (!$course = $DB->get_record('course', array('id' => $forum->course))) {
            print_error('invalidcourseid');
        }
    }

    if (!$context) {
        $context = context_course::instance($course->id);
    }

    // Check whether the discussion is locked.
    /*if (forum_discussion_is_locked($forum, $discussion)) {
        if (!has_capability('mod/forum:canoverridediscussionlock', $context)) {
            return false;
        }
    }*/

    // normal users with temporary guest access can not post, suspended users can not post either
    if (!is_viewing($context, $user->id) and !is_enrolled($context, $user->id, '', true)) {
        return false;
    }

    if ($forum->type == 'news') {
        $capname = 'mod/forum:replynews';
    } else {
        $capname = 'mod/forum:replypost';
    }

    if (!has_capability($capname, $context, $user->id)) {
        return false;
    }

    /*if (!$groupmode = groups_get_activity_groupmode($cm, $course)) {
        return true;
    }*/

    if (has_capability('moodle/site:accessallgroups', $context)) {
        return true;
    }

    if ($groupmode == VISIBLEGROUPS) {
        if ($discussion->groupid == -1) {
            // allow students to reply to all participants discussions - this was not possible in Moodle <1.8
            return true;
        }
        return groups_is_member($discussion->groupid);

    } else {
        //separate groups
        if ($discussion->groupid == -1) {
            return false;
        }
        return groups_is_member($discussion->groupid);
    }
}
// CHAT
function getChatid($componentid){
  global $DB;
  $chat_component=$DB->get_record('chat_components',array('idcomponent'=>$componentid));
  return $chat_component->chatid;
}

//ASSING

function getAssignid($componentid){
  global $DB;
  $assign_component=$DB->get_record('assign_components',array('idcomponent'=>$componentid));
  return $assign_component->assignid;
}

class assignView {

    /** @var stdClass the assignment record that contains the global settings for this assign instance */
    private $instance;

    /** @var grade_item the grade_item record for this assign instance's primary grade item. */
    private $gradeitem;

    /** @var context the context of the course module for this assign instance
     *               (or just the course if we are creating a new one)
     */
    private $context;

    /** @var stdClass the course this assign instance belongs to */
    private $course;

    /** @var stdClass the admin config for all assign instances  */
    private $adminconfig;

    /** @var assign_renderer the custom renderer for this module */
    private $output;

    /** @var cm_info the course module for this assign instance */
    private $coursemodule;

    /** @var array cache for things like the coursemodule name or the scale menu -
     *             only lives for a single request.
     */
    private $cache;

    /** @var array list of the installed submission plugins */
    private $submissionplugins;

    /** @var array list of the installed feedback plugins */
    private $feedbackplugins;

    /** @var string action to be used to return to this page
     *              (without repeating any form submissions etc).
     */
    private $returnaction = 'view';

    /** @var array params to be used to return to this page */
    private $returnparams = array();

    /** @var string modulename prevents excessive calls to get_string */
    private static $modulename = null;

    /** @var string modulenameplural prevents excessive calls to get_string */
    private static $modulenameplural = null;

    /** @var array of marking workflow states for the current user */
    private $markingworkflowstates = null;

    /** @var bool whether to exclude users with inactive enrolment */
    private $showonlyactiveenrol = null;

    /** @var string A key used to identify userlists created by this object. */
    private $useridlistid = null;

    /** @var array cached list of participants for this assignment. The cache key will be group, showactive and the context id */
    private $participants = array();

    /** @var array cached list of user groups when team submissions are enabled. The cache key will be the user. */
    private $usersubmissiongroups = array();

    /** @var array cached list of user groups. The cache key will be the user. */
    private $usergroups = array();

    /** @var array cached list of IDs of users who share group membership with the user. The cache key will be the user. */
    private $sharedgroupmembers = array();

    /**
     * Constructor for the base assign class.
     *
     * Note: For $coursemodule you can supply a stdclass if you like, but it
     * will be more efficient to supply a cm_info object.
     *
     * @param mixed $coursemodulecontext context|null the course module context
     *                                   (or the course context if the coursemodule has not been
     *                                   created yet).
     * @param mixed $coursemodule the current course module if it was already loaded,
     *                            otherwise this class will load one from the context as required.
     * @param mixed $course the current course  if it was already loaded,
     *                      otherwise this class will load one from the context as required.
     */
    public function __construct($coursemodulecontext, $coursemodule, $course) {
        global $SESSION;

        $this->context = $coursemodulecontext;
        $this->course = $course;

        // Ensure that $this->coursemodule is a cm_info object (or null).
        $this->coursemodule = cm_info::create($coursemodule);

        // Temporary cache only lives for a single request - used to reduce db lookups.
        $this->cache = array();

        //$this->submissionplugins = $this->load_plugins('assignsubmission');
        //$this->feedbackplugins = $this->load_plugins('assignfeedback');

        // Extra entropy is required for uniqid() to work on cygwin.
        $this->useridlistid = clean_param(uniqid('', true), PARAM_ALPHANUM);

        if (!isset($SESSION->mod_assign_useridlist)) {
            $SESSION->mod_assign_useridlist = [];
        }
    }
    /**
     * Load the plugins from the sub folders under subtype.
     *
     * @param string $subtype - either submission or feedback
     * @return array - The sorted list of plugins
     */
    /*public function load_plugins($subtype) {
        global $CFG;
        $result = array();

        $names = core_component::get_plugin_list($subtype);

        foreach ($names as $name => $path) {
            if (file_exists($path . '/locallib.php')) {
                require_once($path . '/locallib.php');

                $shortsubtype = substr($subtype, strlen('assign'));
                $pluginclass = 'assign_' . $shortsubtype . '_' . $name;

                $plugin = new $pluginclass($this, $name);

                if ($plugin instanceof assign_plugin) {
                    $idx = $plugin->get_sort_order();
                    while (array_key_exists($idx, $result)) {
                        $idx +=1;
                    }
                    $result[$idx] = $plugin;
                }
            }
        }
        ksort($result);
        return $result;
    }*/

    /**
     * The id used to uniquily identify the cache for this instance of the assign object.
     *
     * @return string
     */
    public function get_useridlist_key_id() {
        return $this->useridlistid;
    }

    /**
     * Update the module completion status (set it viewed).
     *
     * @since Moodle 3.2
     */
    public function set_module_viewed() {
        $completion = new completion_info($this->get_course());
        $completion->set_module_viewed($this->get_course_module());
    }

    /**
     * Get context module.
     *
     * @return context
     */
    public function get_course() {
        return $this->course;
    }

    /**
     * Get the current course module.
     *
     * @return cm_info|null The course module or null if not known
     */
    public function get_course_module() {
        if ($this->coursemodule) {
            return $this->coursemodule;
        }
        if (!$this->context) {
            return null;
        }

        if ($this->context->contextlevel == CONTEXT_MODULE) {
            $modinfo = get_fast_modinfo($this->get_course());
            $this->coursemodule = $modinfo->get_cm($this->context->instanceid);
            return $this->coursemodule;
        }
        return null;
    }
    /**
     * Updates the assign properties with override information for a user.
     *
     * Algorithm:  For each assign setting, if there is a matching user-specific override,
     *   then use that otherwise, if there are group-specific overrides, return the most
     *   lenient combination of them.  If neither applies, leave the assign setting unchanged.
     *
     * @param int $userid The userid.
     */
    public function update_effective_access($userid) {

        $override = $this->override_exists($userid);

        // Merge with assign defaults.
        $keys = array('duedate', 'cutoffdate', 'allowsubmissionsfromdate');
        foreach ($keys as $key) {
            if (isset($override->{$key})) {
                $this->get_instance()->{$key} = $override->{$key};
            }
        }

    }
    /**
     * Returns user override
     *
     * Algorithm:  For each assign setting, if there is a matching user-specific override,
     *   then use that otherwise, if there are group-specific overrides, return the most
     *   lenient combination of them.  If neither applies, leave the assign setting unchanged.
     *
     * @param int $userid The userid.
     * @return override  if exist
     */
    public function override_exists($userid) {
        global $DB;

        // Check for user override.
        $override = $DB->get_record('assign_overrides', array('assignid' => $this->get_instance()->id, 'userid' => $userid));

        if (!$override) {
            $override = new stdClass();
            $override->duedate = null;
            $override->cutoffdate = null;
            $override->allowsubmissionsfromdate = null;
        }

        // Check for group overrides.
        $groupings = groups_get_user_groups($this->get_instance()->course, $userid);

        if (!empty($groupings[0])) {
            // Select all overrides that apply to the User's groups.
            list($extra, $params) = $DB->get_in_or_equal(array_values($groupings[0]));
            $sql = "SELECT * FROM {assign_overrides}
                    WHERE groupid $extra AND assignid = ?";
            $params[] = $this->get_instance()->id;
            $records = $DB->get_records_sql($sql, $params);

            // Combine the overrides.
            $duedates = array();
            $cutoffdates = array();
            $allowsubmissionsfromdates = array();

            foreach ($records as $gpoverride) {
                if (isset($gpoverride->duedate)) {
                    $duedates[] = $gpoverride->duedate;
                }
                if (isset($gpoverride->cutoffdate)) {
                    $cutoffdates[] = $gpoverride->cutoffdate;
                }
                if (isset($gpoverride->allowsubmissionsfromdate)) {
                    $allowsubmissionsfromdates[] = $gpoverride->allowsubmissionsfromdate;
                }
            }
            // If there is a user override for a setting, ignore the group override.
            if (is_null($override->allowsubmissionsfromdate) && count($allowsubmissionsfromdates)) {
                $override->allowsubmissionsfromdate = min($allowsubmissionsfromdates);
            }
            if (is_null($override->cutoffdate) && count($cutoffdates)) {
                if (in_array(0, $cutoffdates)) {
                    $override->cutoffdate = 0;
                } else {
                    $override->cutoffdate = max($cutoffdates);
                }
            }
            if (is_null($override->duedate) && count($duedates)) {
                if (in_array(0, $duedates)) {
                    $override->duedate = 0;
                } else {
                    $override->duedate = max($duedates);
                }
            }

        }

        return $override;
    }
    /**
     * Get the settings for the current instance of this assignment
     *
     * @return stdClass The settings
     */
    public function get_instance() {
        global $DB;
        if ($this->instance) {
            return $this->instance;
        }
        if ($this->get_course_module()) {
            $params = array('id' => $this->get_course_module()->instance);
            $this->instance = $DB->get_record('assign', $params, '*', MUST_EXIST);
        }
        if (!$this->instance) {
            throw new coding_exception('Improper use of the assignment class. ' .
                                       'Cannot load the assignment record.');
        }
        return $this->instance;
    }

    /**
     * Display the assignment, used by view.php
     *
     * The assignment is displayed differently depending on your role,
     * the settings for the assignment and the status of the assignment.
     *
     * @param string $action The current action if any.
     * @param array $args Optional arguments to pass to the view (instead of getting them from GET and POST).
     * @return string - The page output.
     */
    public function view($action='', $args = array()) {
        global $PAGE;

        $o = '';
        $mform = null;
        $notices = array();
        $nextpageparams = array();

        if (!empty($this->get_course_module()->id)) {
            $nextpageparams['id'] = $this->get_course_module()->id;
        }

        // Handle form submissions first.
        if ($action == 'savesubmission') {
            $action = 'editsubmission';
            if ($this->process_save_submission($mform, $notices)) {
                $action = 'redirect';
                $nextpageparams['action'] = 'view';
            }
        } else if ($action == 'editprevioussubmission') {
            $action = 'editsubmission';
            if ($this->process_copy_previous_attempt($notices)) {
                $action = 'redirect';
                $nextpageparams['action'] = 'editsubmission';
            }
        } else if ($action == 'lock') {
            $this->process_lock_submission();
            $action = 'redirect';
            $nextpageparams['action'] = 'grading';
        } else if ($action == 'addattempt') {
            $this->process_add_attempt(required_param('userid', PARAM_INT));
            $action = 'redirect';
            $nextpageparams['action'] = 'grading';
        } else if ($action == 'reverttodraft') {
            $this->process_revert_to_draft();
            $action = 'redirect';
            $nextpageparams['action'] = 'grading';
        } else if ($action == 'unlock') {
            $this->process_unlock_submission();
            $action = 'redirect';
            $nextpageparams['action'] = 'grading';
        } else if ($action == 'setbatchmarkingworkflowstate') {
            $this->process_set_batch_marking_workflow_state();
            $action = 'redirect';
            $nextpageparams['action'] = 'grading';
        } else if ($action == 'setbatchmarkingallocation') {
            $this->process_set_batch_marking_allocation();
            $action = 'redirect';
            $nextpageparams['action'] = 'grading';
        } else if ($action == 'confirmsubmit') {
            $action = 'submit';
            if ($this->process_submit_for_grading($mform, $notices)) {
                $action = 'redirect';
                $nextpageparams['action'] = 'view';
            } else if ($notices) {
                $action = 'viewsubmitforgradingerror';
            }
        } else if ($action == 'submitotherforgrading') {
            if ($this->process_submit_other_for_grading($mform, $notices)) {
                $action = 'redirect';
                $nextpageparams['action'] = 'grading';
            } else {
                $action = 'viewsubmitforgradingerror';
            }
        } else if ($action == 'gradingbatchoperation') {
            $action = $this->process_grading_batch_operation($mform);
            if ($action == 'grading') {
                $action = 'redirect';
                $nextpageparams['action'] = 'grading';
            }
        } else if ($action == 'submitgrade') {
            if (optional_param('saveandshownext', null, PARAM_RAW)) {
                // Save and show next.
                $action = 'grade';
                if ($this->process_save_grade($mform)) {
                    $action = 'redirect';
                    $nextpageparams['action'] = 'grade';
                    $nextpageparams['rownum'] = optional_param('rownum', 0, PARAM_INT) + 1;
                    $nextpageparams['useridlistid'] = optional_param('useridlistid', $this->get_useridlist_key_id(), PARAM_ALPHANUM);
                }
            } else if (optional_param('nosaveandprevious', null, PARAM_RAW)) {
                $action = 'redirect';
                $nextpageparams['action'] = 'grade';
                $nextpageparams['rownum'] = optional_param('rownum', 0, PARAM_INT) - 1;
                $nextpageparams['useridlistid'] = optional_param('useridlistid', $this->get_useridlist_key_id(), PARAM_ALPHANUM);
            } else if (optional_param('nosaveandnext', null, PARAM_RAW)) {
                $action = 'redirect';
                $nextpageparams['action'] = 'grade';
                $nextpageparams['rownum'] = optional_param('rownum', 0, PARAM_INT) + 1;
                $nextpageparams['useridlistid'] = optional_param('useridlistid', $this->get_useridlist_key_id(), PARAM_ALPHANUM);
            } else if (optional_param('savegrade', null, PARAM_RAW)) {
                // Save changes button.
                $action = 'grade';
                if ($this->process_save_grade($mform)) {
                    $action = 'redirect';
                    $nextpageparams['action'] = 'savegradingresult';
                }
            } else {
                // Cancel button.
                $action = 'redirect';
                $nextpageparams['action'] = 'grading';
            }
        } else if ($action == 'quickgrade') {
            $message = $this->process_save_quick_grades();
            $action = 'quickgradingresult';
        } else if ($action == 'saveoptions') {
            $this->process_save_grading_options();
            $action = 'redirect';
            $nextpageparams['action'] = 'grading';
        } else if ($action == 'saveextension') {
            $action = 'grantextension';
            if ($this->process_save_extension($mform)) {
                $action = 'redirect';
                $nextpageparams['action'] = 'grading';
            }
        } else if ($action == 'revealidentitiesconfirm') {
            $this->process_reveal_identities();
            $action = 'redirect';
            $nextpageparams['action'] = 'grading';
        }

        $returnparams = array('rownum'=>optional_param('rownum', 0, PARAM_INT),
                              'useridlistid' => optional_param('useridlistid', $this->get_useridlist_key_id(), PARAM_ALPHANUM));
        $this->register_return_link($action, $returnparams);

        // Include any page action as part of the body tag CSS id.
        if (!empty($action)) {
            $PAGE->set_pagetype('mod-assign-' . $action);
        }
        // Now show the right view page.
        if ($action == 'redirect') {
            $nextpageurl = new moodle_url('/mod/assign/view.php', $nextpageparams);
            redirect($nextpageurl);
            return;
        } else if ($action == 'savegradingresult') {
            $message = get_string('gradingchangessaved', 'assign');
            $o .= $this->view_savegrading_result($message);
        } else if ($action == 'quickgradingresult') {
            $mform = null;
            $o .= $this->view_quickgrading_result($message);
        } else if ($action == 'gradingpanel') {
            $o .= $this->view_single_grading_panel($args);
        } else if ($action == 'grade') {
            $o .= $this->view_single_grade_page($mform);
        } else if ($action == 'viewpluginassignfeedback') {
            $o .= $this->view_plugin_content('assignfeedback');
        } else if ($action == 'viewpluginassignsubmission') {
            $o .= $this->view_plugin_content('assignsubmission');
        } else if ($action == 'editsubmission') {
            $o .= $this->view_edit_submission_page($mform, $notices);
        } else if ($action == 'grader') {
            $o .= $this->view_grader();
        } else if ($action == 'grading') {
            $o .= $this->view_grading_page();
        } else if ($action == 'downloadall') {
            $o .= $this->download_submissions();
        } else if ($action == 'submit') {
            $o .= $this->check_submit_for_grading($mform);
        } else if ($action == 'grantextension') {
            $o .= $this->view_grant_extension($mform);
        } else if ($action == 'revealidentities') {
            $o .= $this->view_reveal_identities_confirm($mform);
        } else if ($action == 'plugingradingbatchoperation') {
            $o .= $this->view_plugin_grading_batch_operation($mform);
        } else if ($action == 'viewpluginpage') {
             $o .= $this->view_plugin_page();
        } else if ($action == 'viewcourseindex') {
             $o .= $this->view_course_index();
        } else if ($action == 'viewbatchsetmarkingworkflowstate') {
             $o .= $this->view_batch_set_workflow_state($mform);
        } else if ($action == 'viewbatchmarkingallocation') {
            $o .= $this->view_batch_markingallocation($mform);
        } else if ($action == 'viewsubmitforgradingerror') {
            $o .= $this->view_error_page(get_string('submitforgrading', 'assign'), $notices);
        } else {
            $o .= $this->view_submission_page();
        }

        return $o;
    }
    /**
     * Set the action and parameters that can be used to return to the current page.
     *
     * @param string $action The action for the current page
     * @param array $params An array of name value pairs which form the parameters
     *                      to return to the current page.
     * @return void
     */
    public function register_return_link($action, $params) {
        global $PAGE;
        $params['action'] = $action;
        $cm = $this->get_course_module();
        if ($cm) {
            $currenturl = new moodle_url('/mod/assign/view.php', array('id' => $cm->id));
        } else {
            $currenturl = new moodle_url('/mod/assign/index.php', array('id' => $this->get_course()->id));
        }

        $currenturl->params($params);
        $PAGE->set_url($currenturl);
    }
    /**
     * View submissions page (contains details of current submission).
     *
     * @return string
     */
    protected function view_submission_page() {
        global $CFG, $DB, $USER, $PAGE;

        $instance = $this->get_instance();

        $o = '';

        $postfix = '';
        if ($this->has_visible_attachments()) {
            $postfix = $this->render_area_files('mod_assign', ASSIGN_INTROATTACHMENT_FILEAREA, 0);
        }
        $o .= $this->get_renderer()->render(new assign_header($instance,
                                                      $this->get_context(),
                                                      $this->show_intro(),
                                                      $this->get_course_module()->id,
                                                      '', '', $postfix));

        // Display plugin specific headers.
        //$plugins = array_merge($this->get_submission_plugins(), $this->get_feedback_plugins());
        foreach ($plugins as $plugin) {
            if ($plugin->is_enabled() && $plugin->is_visible()) {
                $o .= $this->get_renderer()->render(new assign_plugin_header($plugin));
            }
        }

        if ($this->can_view_grades()) {
            // Group selector will only be displayed if necessary.
            $currenturl = new moodle_url('/mod/assign/view.php', array('id' => $this->get_course_module()->id));
            $o .= groups_print_activity_menu($this->get_course_module(), $currenturl->out(), true);

            $summary = $this->get_assign_grading_summary_renderable();
            $o .= $this->get_renderer()->render($summary);
        }
        $grade = $this->get_user_grade($USER->id, false);
        $submission = $this->get_user_submission($USER->id, false);

        if ($this->can_view_submission($USER->id)) {
            $o .= $this->view_student_summary($USER, true);
        }

        $o .= $this->view_footer();

        \mod_assign\event\submission_status_viewed::create_from_assign($this)->trigger();

        return $o;
    }
    /**
     * Count the number of intro attachments.
     *
     * @return int
     */
    protected function count_attachments() {

        $fs = get_file_storage();
        $files = $fs->get_area_files($this->get_context()->id, 'mod_assign', ASSIGN_INTROATTACHMENT_FILEAREA,
                        0, 'id', false);

        return count($files);
    }
    /**
     * Are there any intro attachments to display?
     *
     * @return boolean
     */
    protected function has_visible_attachments() {
        return ($this->count_attachments() > 0);
    }
    /**
     * Get context module.
     *
     * @return context
     */
    public function get_context() {
        return $this->context;
    }
    /**
     * Lazy load the page renderer and expose the renderer to plugins.
     *
     * @return assign_renderer
     */
    public function get_renderer() {
        global $PAGE;
        if ($this->output) {
            return $this->output;
        }
        $this->output = $PAGE->get_renderer('mod_assign', null, RENDERER_TARGET_GENERAL);
        return $this->output;
    }
    /**
     * Based on the current assignment settings should we display the intro.
     *
     * @return bool showintro
     */
    public function show_intro() {
        if ($this->get_instance()->alwaysshowdescription ||
                time() > $this->get_instance()->allowsubmissionsfromdate) {
            return true;
        }
        return false;
    }
    /**
     * Does this user have view grade or grade permission for this assignment?
     *
     * @return bool
     */
    public function can_view_grades() {
        // Permissions check.
        if (!has_any_capability(array('mod/assign:viewgrades', 'mod/assign:grade'), $this->context)) {
            return false;
        }
        // Checks for the edge case when user belongs to no groups and groupmode is sep.
        if ($this->get_course_module()->effectivegroupmode == SEPARATEGROUPS) {
            $groupflag = has_capability('moodle/site:accessallgroups', $this->get_context());
            $groupflag = $groupflag || !empty(groups_get_activity_allowed_groups($this->get_course_module()));
            return (bool)$groupflag;
        }
        return true;
    }
    /**
     * This will retrieve a grade object from the db, optionally creating it if required.
     *
     * @param int $userid The user we are grading
     * @param bool $create If true the grade will be created if it does not exist
     * @param int $attemptnumber The attempt number to retrieve the grade for. -1 means the latest submission.
     * @return stdClass The grade record
     */
    public function get_user_grade($userid, $create, $attemptnumber=-1) {
        global $DB, $USER;

        // If the userid is not null then use userid.
        if (!$userid) {
            $userid = $USER->id;
        }
        $submission = null;

        $params = array('assignment'=>$this->get_instance()->id, 'userid'=>$userid);
        if ($attemptnumber < 0 || $create) {
            // Make sure this grade matches the latest submission attempt.
            if ($this->get_instance()->teamsubmission) {
                $submission = $this->get_group_submission($userid, 0, true, $attemptnumber);
            } else {
                $submission = $this->get_user_submission($userid, true, $attemptnumber);
            }
            if ($submission) {
                $attemptnumber = $submission->attemptnumber;
            }
        }

        if ($attemptnumber >= 0) {
            $params['attemptnumber'] = $attemptnumber;
        }

        $grades = $DB->get_records('assign_grades', $params, 'attemptnumber DESC', '*', 0, 1);

        if ($grades) {
            return reset($grades);
        }
        if ($create) {
            $grade = new stdClass();
            $grade->assignment   = $this->get_instance()->id;
            $grade->userid       = $userid;
            $grade->timecreated = time();
            // If we are "auto-creating" a grade - and there is a submission
            // the new grade should not have a more recent timemodified value
            // than the submission.
            if ($submission) {
                $grade->timemodified = $submission->timemodified;
            } else {
                $grade->timemodified = $grade->timecreated;
            }
            $grade->grade = -1;
            $grade->grader = $USER->id;
            if ($attemptnumber >= 0) {
                $grade->attemptnumber = $attemptnumber;
            }

            $gid = $DB->insert_record('assign_grades', $grade);
            $grade->id = $gid;
            return $grade;
        }
        return false;
    }
    /**
     * Load the submission object for a particular user, optionally creating it if required.
     *
     * For team assignments there are 2 submissions - the student submission and the team submission
     * All files are associated with the team submission but the status of the students contribution is
     * recorded separately.
     *
     * @param int $userid The id of the user whose submission we want or 0 in which case USER->id is used
     * @param bool $create If set to true a new submission object will be created in the database with the status set to "new".
     * @param int $attemptnumber - -1 means the latest attempt
     * @return stdClass The submission
     */
    public function get_user_submission($userid, $create, $attemptnumber=-1) {
        global $DB, $USER;

        if (!$userid) {
            $userid = $USER->id;
        }
        // If the userid is not null then use userid.
        $params = array('assignment'=>$this->get_instance()->id, 'userid'=>$userid, 'groupid'=>0);
        if ($attemptnumber >= 0) {
            $params['attemptnumber'] = $attemptnumber;
        }

        // Only return the row with the highest attemptnumber.
        $submission = null;
        $submissions = $DB->get_records('assign_submission', $params, 'attemptnumber DESC', '*', 0, 1);
        if ($submissions) {
            $submission = reset($submissions);
        }

        if ($submission) {
            return $submission;
        }
        if ($create) {
            $submission = new stdClass();
            $submission->assignment   = $this->get_instance()->id;
            $submission->userid       = $userid;
            $submission->timecreated = time();
            $submission->timemodified = $submission->timecreated;
            $submission->status = ASSIGN_SUBMISSION_STATUS_NEW;
            if ($attemptnumber >= 0) {
                $submission->attemptnumber = $attemptnumber;
            } else {
                $submission->attemptnumber = 0;
            }
            // Work out if this is the latest submission.
            $submission->latest = 0;
            $params = array('assignment'=>$this->get_instance()->id, 'userid'=>$userid, 'groupid'=>0);
            if ($attemptnumber == -1) {
                // This is a new submission so it must be the latest.
                $submission->latest = 1;
            } else {
                // We need to work this out.
                $result = $DB->get_records('assign_submission', $params, 'attemptnumber DESC', 'attemptnumber', 0, 1);
                $latestsubmission = null;
                if ($result) {
                    $latestsubmission = reset($result);
                }
                if (empty($latestsubmission) || ($attemptnumber > $latestsubmission->attemptnumber)) {
                    $submission->latest = 1;
                }
            }
            if ($submission->latest) {
                // This is the case when we need to set latest to 0 for all the other attempts.
                $DB->set_field('assign_submission', 'latest', 0, $params);
            }
            $sid = $DB->insert_record('assign_submission', $submission);
            return $DB->get_record('assign_submission', array('id' => $sid));
        }
        return false;
    }
    /**
     * Perform an access check to see if the current $USER can view this users submission.
     *
     * @param int $userid
     * @return bool
     */
    public function can_view_submission($userid) {
        global $USER;

        if (!$this->is_active_user($userid) && !has_capability('moodle/course:viewsuspendedusers', $this->context)) {
            return false;
        }
        if (!is_enrolled($this->get_course_context(), $userid)) {
            return false;
        }
        if (has_any_capability(array('mod/assign:viewgrades', 'mod/assign:grade'), $this->context)) {
            return true;
        }
        if ($userid == $USER->id && has_capability('mod/assign:submit', $this->context)) {
            return true;
        }
        return false;
    }
    /**
     * Return true is user is active user in course else false
     *
     * @param int $userid
     * @return bool true is user is active in course.
     */
    public function is_active_user($userid) {
        return !in_array($userid, get_suspended_userids($this->context, true));
    }
    /**
     * Get the context of the current course.
     *
     * @return mixed context|null The course context
     */
    public function get_course_context() {
        if (!$this->context && !$this->course) {
            throw new coding_exception('Improper use of the assignment class. ' .
                                       'Cannot load the course context.');
        }
        if ($this->context) {
            return $this->context->get_course_context();
        } else {
            return context_course::instance($this->course->id);
        }
    }
    /**
     * Print 2 tables of information with no action links -
     * the submission summary and the grading summary.
     *
     * @param stdClass $user the user to print the report for
     * @param bool $showlinks - Return plain text or links to the profile
     * @return string - the html summary
     */
    public function view_student_summary($user, $showlinks) {

        $o = '';

        if ($this->can_view_submission($user->id)) {

            if (has_capability('mod/assign:submit', $this->get_context(), $user, false)) {
                $submissionstatus = $this->get_assign_submission_status_renderable($user, $showlinks);
                $o .= $this->get_renderer()->render($submissionstatus);
            }

            // If there is a visible grade, show the feedback.
            $feedbackstatus = $this->get_assign_feedback_status_renderable($user);
            if ($feedbackstatus) {
                $o .= $this->get_renderer()->render($feedbackstatus);
            }

            // If there is more than one submission, show the history.
            $history = $this->get_assign_attempt_history_renderable($user);
            if (count($history->submissions) > 1) {
                $o .= $this->get_renderer()->render($history);
            }
        }
        return $o;
    }
    /**
     * Creates an assign_submission_status renderable.
     *
     * @param stdClass $user the user to get the report for
     * @param bool $showlinks return plain text or links to the profile
     * @return assign_submission_status renderable object
     */
    public function get_assign_submission_status_renderable($user, $showlinks) {
        global $PAGE;

        $instance = $this->get_instance();
        $flags = $this->get_user_flags($user->id, false);
        $submission = $this->get_user_submission($user->id, false);

        $teamsubmission = null;
        $submissiongroup = null;
        $notsubmitted = array();
        if ($instance->teamsubmission) {
            $teamsubmission = $this->get_group_submission($user->id, 0, false);
            $submissiongroup = $this->get_submission_group($user->id);
            $groupid = 0;
            if ($submissiongroup) {
                $groupid = $submissiongroup->id;
            }
            $notsubmitted = $this->get_submission_group_members_who_have_not_submitted($groupid, false);
        }

        $showedit = $showlinks &&
                    ($this->is_any_submission_plugin_enabled()) &&
                    $this->can_edit_submission($user->id);

        $gradelocked = ($flags && $flags->locked) || $this->grading_disabled($user->id, false);

        // Grading criteria preview.
        $gradingmanager = get_grading_manager($this->context, 'mod_assign', 'submissions');
        $gradingcontrollerpreview = '';
        if ($gradingmethod = $gradingmanager->get_active_method()) {
            $controller = $gradingmanager->get_controller($gradingmethod);
            if ($controller->is_form_defined()) {
                $gradingcontrollerpreview = $controller->render_preview($PAGE);
            }
        }

        $showsubmit = ($showlinks && $this->submissions_open($user->id));
        $showsubmit = ($showsubmit && $this->show_submit_button($submission, $teamsubmission, $user->id));

        $extensionduedate = null;
        if ($flags) {
            $extensionduedate = $flags->extensionduedate;
        }
        $viewfullnames = has_capability('moodle/site:viewfullnames', $this->get_context());

        $gradingstatus = $this->get_grading_status($user->id);
        $usergroups = $this->get_all_groups($user->id);
        $submissionstatus = new assign_submission_status($instance->allowsubmissionsfromdate,
                                                          $instance->alwaysshowdescription,
                                                          $submission,
                                                          $instance->teamsubmission,
                                                          $teamsubmission,
                                                          $submissiongroup,
                                                          $notsubmitted,
                                                          $this->is_any_submission_plugin_enabled(),
                                                          $gradelocked,
                                                          $this->is_graded($user->id),
                                                          $instance->duedate,
                                                          $instance->cutoffdate,
                                                          $this->get_submission_plugins(),
                                                          $this->get_return_action(),
                                                          $this->get_return_params(),
                                                          $this->get_course_module()->id,
                                                          $this->get_course()->id,
                                                          assign_submission_status::STUDENT_VIEW,
                                                          $showedit,
                                                          $showsubmit,
                                                          $viewfullnames,
                                                          $extensionduedate,
                                                          $this->get_context(),
                                                          $this->is_blind_marking(),
                                                          $gradingcontrollerpreview,
                                                          $instance->attemptreopenmethod,
                                                          $instance->maxattempts,
                                                          $gradingstatus,
                                                          $instance->preventsubmissionnotingroup,
                                                          $usergroups);
        return $submissionstatus;
    }
    /**
     * This will retrieve a user flags object from the db optionally creating it if required.
     * The user flags was split from the user_grades table in 2.5.
     *
     * @param int $userid The user we are getting the flags for.
     * @param bool $create If true the flags record will be created if it does not exist
     * @return stdClass The flags record
     */
    public function get_user_flags($userid, $create) {
        global $DB, $USER;

        // If the userid is not null then use userid.
        if (!$userid) {
            $userid = $USER->id;
        }

        $params = array('assignment'=>$this->get_instance()->id, 'userid'=>$userid);

        $flags = $DB->get_record('assign_user_flags', $params);

        if ($flags) {
            return $flags;
        }
        if ($create) {
            $flags = new stdClass();
            $flags->assignment = $this->get_instance()->id;
            $flags->userid = $userid;
            $flags->locked = 0;
            $flags->extensionduedate = 0;
            $flags->workflowstate = '';
            $flags->allocatedmarker = 0;

            // The mailed flag can be one of 3 values: 0 is unsent, 1 is sent and 2 is do not send yet.
            // This is because students only want to be notified about certain types of update (grades and feedback).
            $flags->mailed = 2;

            $fid = $DB->insert_record('assign_user_flags', $flags);
            $flags->id = $fid;
            return $flags;
        }
        return false;
    }
    /**
     * Check if submission plugins installed are enabled.
     *
     * @return bool
     */
    public function is_any_submission_plugin_enabled() {
        if (!isset($this->cache['any_submission_plugin_enabled'])) {
            $this->cache['any_submission_plugin_enabled'] = false;
            foreach ($this->submissionplugins as $plugin) {
                if ($plugin->is_enabled() && $plugin->is_visible() && $plugin->allow_submissions()) {
                    $this->cache['any_submission_plugin_enabled'] = true;
                    break;
                }
            }
        }

        return $this->cache['any_submission_plugin_enabled'];

    }
    /**
     * Determine if this users grade can be edited.
     *
     * @param int $userid - The student userid
     * @param bool $checkworkflow - whether to include a check for the workflow state.
     * @return bool $gradingdisabled
     */
    public function grading_disabled($userid, $checkworkflow=true) {
        global $CFG;
        if ($checkworkflow && $this->get_instance()->markingworkflow) {
            $grade = $this->get_user_grade($userid, false);
            $validstates = $this->get_marking_workflow_states_for_current_user();
            if (!empty($grade) && !empty($grade->workflowstate) && !array_key_exists($grade->workflowstate, $validstates)) {
                return true;
            }
        }
        $gradinginfo = grade_get_grades($this->get_course()->id,
                                        'mod',
                                        'assign',
                                        $this->get_instance()->id,
                                        array($userid));
        if (!$gradinginfo) {
            return false;
        }

        if (!isset($gradinginfo->items[0]->grades[$userid])) {
            return false;
        }
        $gradingdisabled = $gradinginfo->items[0]->grades[$userid]->locked ||
                           $gradinginfo->items[0]->grades[$userid]->overridden;
        return $gradingdisabled;
    }
    /**
     * Is this assignment open for submissions?
     *
     * Check the due date,
     * prevent late submissions,
     * has this person already submitted,
     * is the assignment locked?
     *
     * @param int $userid - Optional userid so we can see if a different user can submit
     * @param bool $skipenrolled - Skip enrollment checks (because they have been done already)
     * @param stdClass $submission - Pre-fetched submission record (or false to fetch it)
     * @param stdClass $flags - Pre-fetched user flags record (or false to fetch it)
     * @param stdClass $gradinginfo - Pre-fetched user gradinginfo record (or false to fetch it)
     * @return bool
     */
    public function submissions_open($userid = 0,
                                     $skipenrolled = false,
                                     $submission = false,
                                     $flags = false,
                                     $gradinginfo = false) {
        global $USER;

        if (!$userid) {
            $userid = $USER->id;
        }

        $time = time();
        $dateopen = true;
        $finaldate = false;
        if ($this->get_instance()->cutoffdate) {
            $finaldate = $this->get_instance()->cutoffdate;
        }

        if ($flags === false) {
            $flags = $this->get_user_flags($userid, false);
        }
        if ($flags && $flags->locked) {
            return false;
        }

        // User extensions.
        if ($finaldate) {
            if ($flags && $flags->extensionduedate) {
                // Extension can be before cut off date.
                if ($flags->extensionduedate > $finaldate) {
                    $finaldate = $flags->extensionduedate;
                }
            }
        }

        if ($finaldate) {
            $dateopen = ($this->get_instance()->allowsubmissionsfromdate <= $time && $time <= $finaldate);
        } else {
            $dateopen = ($this->get_instance()->allowsubmissionsfromdate <= $time);
        }

        if (!$dateopen) {
            return false;
        }

        // Now check if this user has already submitted etc.
        if (!$skipenrolled && !is_enrolled($this->get_course_context(), $userid)) {
            return false;
        }
        // Note you can pass null for submission and it will not be fetched.
        if ($submission === false) {
            if ($this->get_instance()->teamsubmission) {
                $submission = $this->get_group_submission($userid, 0, false);
            } else {
                $submission = $this->get_user_submission($userid, false);
            }
        }
        if ($submission) {

            if ($this->get_instance()->submissiondrafts && $submission->status == ASSIGN_SUBMISSION_STATUS_SUBMITTED) {
                // Drafts are tracked and the student has submitted the assignment.
                return false;
            }
        }

        // See if this user grade is locked in the gradebook.
        if ($gradinginfo === false) {
            $gradinginfo = grade_get_grades($this->get_course()->id,
                                            'mod',
                                            'assign',
                                            $this->get_instance()->id,
                                            array($userid));
        }
        if ($gradinginfo &&
                isset($gradinginfo->items[0]->grades[$userid]) &&
                $gradinginfo->items[0]->grades[$userid]->locked) {
            return false;
        }

        return true;
    }
    /**
     * Returns true if the submit subsission button should be shown to the user.
     *
     * @param stdClass $submission The users own submission record.
     * @param stdClass $teamsubmission The users team submission record if there is one
     * @param int $userid The user
     * @return bool
     */
    protected function show_submit_button($submission = null, $teamsubmission = null, $userid = null) {
        if ($teamsubmission) {
            if ($teamsubmission->status === ASSIGN_SUBMISSION_STATUS_SUBMITTED) {
                // The assignment submission has been completed.
                return false;
            } else if ($this->submission_empty($teamsubmission)) {
                // There is nothing to submit yet.
                return false;
            } else if ($submission && $submission->status === ASSIGN_SUBMISSION_STATUS_SUBMITTED) {
                // The user has already clicked the submit button on the team submission.
                return false;
            } else if (
                !empty($this->get_instance()->preventsubmissionnotingroup)
                && $this->get_submission_group($userid) == false
            ) {
                return false;
            }
        } else if ($submission) {
            if ($submission->status === ASSIGN_SUBMISSION_STATUS_SUBMITTED) {
                // The assignment submission has been completed.
                return false;
            } else if ($this->submission_empty($submission)) {
                // There is nothing to submit.
                return false;
            }
        } else {
            // We've not got a valid submission or team submission.
            return false;
        }
        // Last check is that this instance allows drafts.
        return $this->get_instance()->submissiondrafts;
    }
    /**
     * Determine if the current submission is empty or not.
     *
     * @param submission $submission the students submission record to check.
     * @return bool
     */
    public function submission_empty($submission) {
        $allempty = true;

        foreach ($this->submissionplugins as $plugin) {
            if ($plugin->is_enabled() && $plugin->is_visible()) {
                if (!$allempty || !$plugin->is_empty($submission)) {
                    $allempty = false;
                }
            }
        }
        return $allempty;
    }
    /**
     * Returns the grading status.
     *
     * @param int $userid the user id
     * @return string returns the grading status
     */
    public function get_grading_status($userid) {
        if ($this->get_instance()->markingworkflow) {
            $flags = $this->get_user_flags($userid, false);
            if (!empty($flags->workflowstate)) {
                return $flags->workflowstate;
            }
            return ASSIGN_MARKING_WORKFLOW_STATE_NOTMARKED;
        } else {
            $attemptnumber = optional_param('attemptnumber', -1, PARAM_INT);
            $grade = $this->get_user_grade($userid, false, $attemptnumber);

            if (!empty($grade) && $grade->grade !== null && $grade->grade >= 0) {
                return ASSIGN_GRADING_STATUS_GRADED;
            } else {
                return ASSIGN_GRADING_STATUS_NOT_GRADED;
            }
        }
    }
    /**
     * Gets all groups the user is a member of.
     *
     * @param int $userid Teh id of the user who's groups we are checking
     * @return array The group objects
     */
    public function get_all_groups($userid) {
        if (isset($this->usergroups[$userid])) {
            return $this->usergroups[$userid];
        }

        $grouping = $this->get_instance()->teamsubmissiongroupingid;
        $return = groups_get_all_groups($this->get_course()->id, $userid, $grouping);

        $this->usergroups[$userid] = $return;

        return $return;
    }
    /**
     * See if this assignment has a grade yet.
     *
     * @param int $userid
     * @return bool
     */
    protected function is_graded($userid) {
        $grade = $this->get_user_grade($userid, false);
        if ($grade) {
            return ($grade->grade !== null && $grade->grade >= 0);
        }
        return false;
    }
    /**
     * Get list of submission plugins installed.
     *
     * @return array
     */
    public function get_submission_plugins() {
        return $this->submissionplugins;
    }
    /**
     * Return an action that can be used to get back to the current page.
     *
     * @return string action
     */
    public function get_return_action() {
        global $PAGE;

        // Web services don't set a URL, we should avoid debugging when ussing the url object.
        if (!WS_SERVER) {
            $params = $PAGE->url->params();
        }

        if (!empty($params['action'])) {
            return $params['action'];
        }
        return '';
    }
    /**
     * Return a list of parameters that can be used to get back to the current page.
     *
     * @return array params
     */
    public function get_return_params() {
        global $PAGE;

        $params = $PAGE->url->params();
        unset($params['id']);
        unset($params['action']);
        return $params;
    }
    /**
     * Is blind marking enabled and reveal identities not set yet?
     *
     * @return bool
     */
    public function is_blind_marking() {
        return $this->get_instance()->blindmarking && !$this->get_instance()->revealidentities;
    }
    /**
     * Creates an assign_feedback_status renderable.
     *
     * @param stdClass $user the user to get the report for
     * @return assign_feedback_status renderable object
     */
    public function get_assign_feedback_status_renderable($user) {
        global $CFG, $DB, $PAGE;

        require_once($CFG->libdir.'/gradelib.php');
        require_once($CFG->dirroot.'/grade/grading/lib.php');

        $instance = $this->get_instance();
        $grade = $this->get_user_grade($user->id, false);
        $gradingstatus = $this->get_grading_status($user->id);

        $gradinginfo = grade_get_grades($this->get_course()->id,
                                    'mod',
                                    'assign',
                                    $instance->id,
                                    $user->id);

        $gradingitem = null;
        $gradebookgrade = null;
        if (isset($gradinginfo->items[0])) {
            $gradingitem = $gradinginfo->items[0];
            $gradebookgrade = $gradingitem->grades[$user->id];
        }

        // Check to see if all feedback plugins are empty.
        $emptyplugins = true;
        if ($grade) {
            foreach ($this->get_feedback_plugins() as $plugin) {
                if ($plugin->is_visible() && $plugin->is_enabled()) {
                    if (!$plugin->is_empty($grade)) {
                        $emptyplugins = false;
                    }
                }
            }
        }

        if ($this->get_instance()->markingworkflow && $gradingstatus != ASSIGN_MARKING_WORKFLOW_STATE_RELEASED) {
            $emptyplugins = true; // Don't show feedback plugins until released either.
        }

        $cangrade = has_capability('mod/assign:grade', $this->get_context());
        // If there is a visible grade, show the summary.
        if (!is_null($gradebookgrade) && (!is_null($gradebookgrade->grade) || !$emptyplugins)
                && ($cangrade || !$gradebookgrade->hidden)) {

            $gradefordisplay = null;
            $gradeddate = null;
            $grader = null;
            $gradingmanager = get_grading_manager($this->get_context(), 'mod_assign', 'submissions');

            // Only show the grade if it is not hidden in gradebook.
            if (!is_null($gradebookgrade->grade) && ($cangrade || !$gradebookgrade->hidden)) {
                if ($controller = $gradingmanager->get_active_controller()) {
                    $menu = make_grades_menu($this->get_instance()->grade);
                    $controller->set_grade_range($menu, $this->get_instance()->grade > 0);
                    $gradefordisplay = $controller->render_grade($PAGE,
                                                                 $grade->id,
                                                                 $gradingitem,
                                                                 $gradebookgrade->str_long_grade,
                                                                 $cangrade);
                } else {
                    $gradefordisplay = $this->display_grade($gradebookgrade->grade, false);
                }
                $gradeddate = $gradebookgrade->dategraded;
                if (isset($grade->grader)) {
                    $grader = $DB->get_record('user', array('id' => $grade->grader));
                }
            }

            $viewfullnames = has_capability('moodle/site:viewfullnames', $this->get_context());

            $feedbackstatus = new assign_feedback_status($gradefordisplay,
                                                  $gradeddate,
                                                  $grader,
                                                  $this->get_feedback_plugins(),
                                                  $grade,
                                                  $this->get_course_module()->id,
                                                  $this->get_return_action(),
                                                  $this->get_return_params(),
                                                  $viewfullnames);
            return $feedbackstatus;
        }
        return;
    }
    /**
     * Creates an assign_attempt_history renderable.
     *
     * @param stdClass $user the user to get the report for
     * @return assign_attempt_history renderable object
     */
    public function get_assign_attempt_history_renderable($user) {

        $allsubmissions = $this->get_all_submissions($user->id);
        $allgrades = $this->get_all_grades($user->id);

        $history = new assign_attempt_history($allsubmissions,
                                              $allgrades,
                                              $this->get_submission_plugins(),
                                              $this->get_feedback_plugins(),
                                              $this->get_course_module()->id,
                                              $this->get_return_action(),
                                              $this->get_return_params(),
                                              false,
                                              0,
                                              0);
        return $history;
    }
}
