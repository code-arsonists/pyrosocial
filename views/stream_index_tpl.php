<!----- STREAM COMPOSER ------>
<div id="st_comp_wrap">
	<form name="st_comp_frm" class="st_comp_frm" method="post" action="/pyrosocial/streams">
		<div class="st_comp_type">
			<b>Filters: </b>
			<span data-type="update" class="fbut mfilter">Update</span>
			<span data-type="photo" class="fbut mfilter">Photo</span>
			<span data-type="video" class="fbut mfilter">Video</span>
			<span data-type="link" class="fbut mfilter">Link</span>
		</div>
		<div class="st_comp_box">
			<textarea cols="60" style="height: 20px; overflow: hidden;" name="sbody" id="sbody" class="input"></textarea>
			<input class="button" style="float:right; margin-top:7px; margin-right:7px;" id="shareButton" value="Share" />
		</div>
	</form>
</div>


<br clear="all">

<div id="st_feed_wrap">
    
<!-----  STREAM FEED ------>
<div id="record-21" class="st_feed_item friends_area">
	<img class="st_feed_ico"  alt="" style="float:left;" src="http://www.gravatar.com/avatar/43e5f0f9162386349ded87d9f7d1333f?s=35&r=x">

	<div class="content">
		<b><a href="profiles/members/view/2">Jobob Fisherman</a></b>

		<p class="body">body</p>

		<abbr title="2011-09-04T22:53:07Q" class="timeago">1 hour ago</abbr>
		<span data-id="21" class="st_feed_like stream_like">Like</span>
	</div>
	
	<!-----  COMMENTS FEED ------>
	<div class="st_com_wrap" id="CommentPosted21" style="display: block;">
		
		<!-----  COMMENTS ITEM ------>
		<div align="left" class="st_com_item" id="record-24">
			<img class="st_com_ico" src="http://www.gravatar.com/avatar/9404c9c05dc1ffa15b285f0eb5613ddc?s=25&r=x" style="float:left; width:25px;height:25px;" alt="">
			<a class="name" href="http://127.0.0.1/pyro/profiles/members/view/1">ryun</a>
			<p class="body">yeah buddy!</p>
			<abbr title="2011-09-04T22:53:07Q" class="timeago">1 hour ago</abbr>
			<a class="c_delete" id="CID-24" href="#">Delete</a>
		</div>
		
	</div>

	<!-----  COMMENTS BOX ------>
	<div align="right" id="commentBox-21" class="commentBox">
		<img alt="" style="float:left;" class="CommentImg" src="http://www.gravatar.com/avatar/9404c9c05dc1ffa15b285f0eb5613ddc?s=25&r=x">
		<label id="record-21">
			<textarea cols="60" name="commentMark" id="commentMark-21" class="commentMark" style="overflow: hidden; height: 15px; color: rgb(51, 51, 51);"></textarea>
		</label>
	</div>
</div>
            
    
</div>
<link rel="stylesheet" type="text/css" href="/pyro/addons/shared_addons/modules/profiles/css/facebox.css">
<link rel="stylesheet" type="text/css" href="/pyro/addons/shared_addons/modules/profiles/css/dependencies/screen.css">
<script src="/pyro/addons/shared_addons/modules/profiles/js/jquery.elastic.js" type="text/javascript"></script>
<script src="/pyro/addons/shared_addons/modules/profiles/js/jquery.watermarkinput.js" type="text/javascript"></script>

<script type="text/javascript">


    /*
     * JavaScript Pretty Date
     * Copyright (c) 2008 John Resig (jquery.com)
     * Licensed under the MIT license.
     */

    // Takes an ISO time and returns a string representing how
    // long ago the date represents.
    function prettyDate(time){
        var date = new Date((time || "").replace(/-/g,"/").replace(/[TZQ]/g," ")),
        diff = (((new Date()).getTime() - date.getTime()) / 1000),
        day_diff = Math.floor(diff / 86400);

        if ( isNaN(day_diff) || day_diff < 0 || day_diff >= 31 )return;

        if (day_diff >= 1)
        {
            var hours = date.getHours()
            var minutes = date.getMinutes()

            var suffix = "AM";
            if (hours >= 12) {
            suffix = "PM";
            hours = hours - 12;
            }
            if (hours == 0) {
            hours = 12;
            }

            if (minutes < 10)
            minutes = "0" + minutes;
            var tpl_time = " at " + hours + ":" + minutes + " " + suffix;
        }

        return day_diff == 0 && (
        diff < 60 && "just now" ||
            diff < 120 && "1 minute ago" ||
            diff < 3600 && Math.floor( diff / 60 ) + " minutes ago" ||
            diff < 7200 && "1 hour ago" ||
            diff < 86400 && Math.floor( diff / 3600 ) + " hours ago") ||
            day_diff == 1 && "Yesterday"+tpl_time ||
            day_diff < 7 && day_diff + " days ago"+tpl_time ||
            day_diff < 31 && Math.ceil( day_diff / 7 ) + " weeks ago"+tpl_time;
    }

    if ( typeof jQuery != "undefined" )	jQuery.fn.prettyDate = function(){return this.each(function(){var date = prettyDate(this.title);if ( date ) jQuery(this).text( date );});};

    jQuery(document).ready(function($){
        /*=== Update Pretty Date ============================================== */
        $(".timeago").prettyDate();
        setInterval(function(){ $(".timeago").prettyDate(); }, 5000);

        /*=== Post Stream ============================================== */

        $('#shareButton').live('click',function(){

            if ($('#sbody').val() != "What's on your mind?")
            {
                $.post("profiles/streams/update", {
                    type: $('.stype span.selected').attr('data-filter'),
                    body: $('#sbody').val()
                }, function(response){
                    str = '<div id="record-'+response.id+'" class="friends_area"><img alt="" style="float:left; width:25px;height:25px;" src="'+response.avatar+'">';
                    str += '<label class="name" style="float:left"><b><a href="profiles/members/view/'+response.user_id+'">'+response.full_name+'</a></b>';
                    str += '<em>'+response.body+'</em><br clear="all"><span class="timeago" title="'+response.created_iso8601+'">'+response.created_on+'</span> &nbsp; ';
                    str += '<span class="stream_like" data-id="'+response.id+'"></span> <a class="showCommentBox" id="post_id'+response.id+'" href="javascript: void(0)">Comments</a></label><a class="delete" href="#" style="display: none;">X</a>';
                    str += '<br clear="all" />';
                    str += '<div id="CommentPosted'+response.id+'" class="scomments"></div>';
                    str += '<div class="commentBox" align="right" id="commentBox-'+response.id+'">';
                    str += '<img src="'+response.avatar+'" class="CommentImg" style="float:left;" alt="" />';
                    str += '<label id="record-'+response.id+'">';
                    str += '<textarea class="commentMark" id="commentMark-'+response.id+'" name="commentMark" cols="60"></textarea>';
                    str += '</label>';
                    str += '<br clear="all" />';
                    str += '</div>';
                    str += '</div>';
                    str += '<br clear="both" />';

                    // Create Template for response
                    $('#posting').prepend(str).fadeIn('slow');
                    $("#sbody").val("What's on your mind?");
                }, 'json');
            }
            return false;
        });


        /*	$('.commentMark').live("focus", function(e){

                var parent  = $('.commentMark').parent();
                $(".commentBox").children(".commentMark").css('width','320px');
                $(".commentBox").children("a#SubmitComment").hide();
                $(".commentBox").children(".CommentImg").hide();

                var getID =  parent.attr('id').replace('record-','');
                $("#commentBox-"+getID).children("a#SubmitComment").show();
                $('.commentMark').css('width','300px');
                $("#commentBox-"+getID).children(".CommentImg").show();
        });
         */
        //showCommentBox
        $('a.showCommentBox').live("click", function(e){

            var getpID =  $(this).attr('id').replace('post_id','');

            $("#commentBox-"+getpID).css('display','');
            $("#commentMark-"+getpID).focus();
            $("#commentBox-"+getpID).children("CommentImg").show();
            $("#commentBox-"+getpID).children("a#SubmitComment").show();
        });

        /*=== Post Comment ============================================== */
        $('.commentMark').live('keypress', function(e) {
            if(e.which == 13){


                //$('a.comment').live("click", function(e){

                var getpID =  $(this).parent().attr('id').replace('record-','');
                var comment_text = $("#commentMark-"+getpID).val();

                if(comment_text != "Write a comment...")
                {
                    $.post("profiles/streams/comment", {'comment':comment_text,"stream_id":getpID}, function(response){
                        var str ='';
                        console.log(response.comments);
                        for (i in response.comments)
                        {
                            str += '<div align="left" id="record-'+i+'" class="commentPanel"><img alt="" style="float:left; width:25px;height:25px;" src="'+response.comments[i].avatar+'">';
                            str += '<span class="name" style="float:left"><b><a href="http://127.0.0.1/pyro/profiles/members/view/'+response.comments[i].user_id+'">'+response.comments[i].username+'</a></b></span>';
                            str += '<p class="postedComments">'+response.comments[i].body+'</p><br clear="all"><span class="timeago" title="'+response.comments[i].created_iso860+'" style="margin-left:43px; color:#666666; font-size:11px">'+prettyDate(response.comments[i].created_iso860)+'</span>';
                            if (response.comments[i].is_author)
                            {
                                str += '&nbsp;&nbsp;<a href="#" id="CID-'+i+'" class="c_delete">Delete</a>';
                            }
                            str += '</div>';
                        }
                        // Create Template for response
                        //$('#posting').prepend(str).fadeIn('slow');

                        $('#CommentPosted'+getpID).fadeOut().html(str).fadeIn('slow');
                        $("#commentMark-"+getpID).val("Write a comment...");
                    }, 'json');
                }
                return false;
            }
        });

        /*=== Show Comment ============================================== */
        $('.show_all_comments').live("click", function(e){

            var sid =  $(this).attr('data-id');
            var ccount =  $(this).attr('data-count');
            $.post("profiles/streams/show_comments/"+sid+"/"+ccount,{}, function(response){
                var str ='';
                console.log(response.comments);
                for (i in response.comments)
                {
                    str += '<div align="left" id="record-'+i+'" class="commentPanel"><img alt="" style="float:left; width:25px;height:25px;" src="'+response.comments[i].avatar+'">';
                    str += '<span class="name" style="float:left"><b><a href="http://127.0.0.1/pyro/profiles/members/view/'+response.comments[i].user_id+'">'+response.comments[i].username+'</a></b></span>';
                    str += '<p class="postedComments">'+response.comments[i].body+'</p><br clear="all"><span style="margin-left:43px; color:#666666; font-size:11px">'+response.comments[i].created_on+'</span>';
                    if (response.comments[i].is_author)
                    {
                        str += '&nbsp;&nbsp;<a href="#" id="CID-'+i+'" class="c_delete">Delete</a>';
                    }
                    str += '</div>';
                }
                $('#CommentPosted'+sid).fadeOut().html(str).fadeIn('slow');
                $("#commentMark-"+sid).val("Write a comment...");
            }, 'json');

            return false;
        });

        /*=== Like stream or comment ============================================== */
        $(".stream_like, .stream_unlike").live('click',function(){
            //get the id
            var me = $(this);
            var stream_id = $(this).attr('data-id');
            var is_like = $(this).hasClass('stream_like') ? true : false;
            var is_comment = $(this).hasClass('stream_comment') ? true : false;

            var _req = {'stream_id': stream_id };

            if (is_like)
            {
                _req.action = 'like';
                me.removeClass('stream_like');
                me.addClass('stream_unlike');
            }
            else {
                _req.action = 'unlike';
                me.removeClass('stream_unlike');
                me.addClass('stream_like');
            }

            _req.ltype = (is_comment) ? 1:0;

            //fadeout the vote-count
            //$("span#votes_count"+the_id).fadeOut("fast");

            //the main ajax request
            $.post('profiles/streams/new_like', _req, function(msg) {
                me.html('unlike');

                //fadein the vote count
                //$("span#votes_count"+the_id).fadeIn();
                //remove the spinner
                //$("span#vote_buttons"+the_id).remove();
            });
        });

        /*=== Del Comment ============================================== */
        $('a.c_delete').live("click", function(e){

            if(confirm('Are you sure you want to delete this comment?')==false)
                return false;

            e.preventDefault();
            var parent  = $('a.c_delete').parent();
            var c_id =  $(this).attr('id').replace('CID-','');

            $.ajax({

                type: 'get',

                url: 'profiles/streams/del_comment/'+ c_id,

                data: '',

                beforeSend: function(){

                },

                success: function(){

                    parent.fadeOut(200,function(){

                        parent.remove();

                    });

                }

            });
        });

        /// hover show remove button
        $('.friends_area').live("mouseenter", function(e){
            $(this).children("a.delete").show();
        });
        $('.friends_area').live("mouseleave", function(e){
            $('a.delete').hide();
        });
        /// hover show remove button

        /*=== Del Stream ============================================== */
        $('a.delete').live("click", function(e){

            if(confirm('Are you sure you want to delete this post?')==false)

            return false;

        e.preventDefault();

        var parent  = $('a.delete').parent();

        var temp    = parent.attr('id').replace('record-','');

        var main_tr = $('#'+temp).parent();

        $.ajax({

            type: 'get',

            url: 'profiles/streams/del_stream/'+ parent.attr('id').replace('record-',''),

            data: '',

            beforeSend: function(){

            },

            success: function(){

                parent.fadeOut(200,function(){

                    main_tr.remove();

                });

            }

        });

    });

    $('textarea').elastic();

    jQuery(function($){

        $("#watermark").Watermark("What's on your mind?");
        $(".commentMark").Watermark("Write a comment...");

    });

    jQuery(function($){

        $("#watermark").Watermark("watermark","#369");
        $(".commentMark").Watermark("watermark","#EEEEEE");

    });

    function UseData(){

        $.Watermark.HideAll();

        //Do Stuff

        $.Watermark.ShowAll();

    }

});

// ]]>

</script>

	</section>
</div>