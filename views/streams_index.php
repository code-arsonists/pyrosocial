<form id="frmStream" class="frm-streams" action="pyrosocial/update" method="post" name="postsForm" enctype="multipart/form-data">
    <?php echo form_hidden($this->security->get_csrf_token_name(), $this->security->get_csrf_hash()); ?>
    <br />
    <div class="stype">
        <b>Share: </b>
        <span class="fbut mfilter active" data-filter="update">Status</span>
        <span class="fbut mfilter" data-filter="chkin">Check-In</span>
        <span class="fbut mfilter" data-filter="img">Photo</span>
        <span class="fbut mfilter" data-filter="video">Video</span>
        <span class="fbut mfilter" data-filter="link">Link</span>
    </div>
    <div class="UIComposer_Box">
        <textarea class="" id="sbody" name="body" style="height:20px" cols="60"></textarea>
        <div id="stream-frm-chkin" class="stream-frm-area" style="display: none;">
            <div id="listing-address">
                <input id="latitude" name="loc_lat" type="hidden" value=""/>
                <input id="longitude" name="loc_lon" type="hidden" value=""/>
                <p class="_100">
                    <label>
                        Location (<i>
                            <smaller>
                                Address, City, State, or Zipcode
                            </smaller></i>)</label>
                    <input id="address" type="text" class="tip-s" title="Example: 123 Main, 12345"/>
                </p>
                <p class="_100">
                    <label>
                        Place</label>
                    <input id="searchTextField" name="location" type="text" size="50" placeholder="Search places near ">
                </p>
                <p>
                    <label>
                        Type of Place</label>
                    <input type="radio" name="type" id="changetype-all" checked="checked">
                    All &nbsp;&nbsp;
                    <input type="radio" name="type" id="changetype-establishment">
                    Establishments &nbsp;&nbsp;
                    <input type="radio" name="type" id="changetype-geocode">
                    Geocodes
                </p>
            </div>
            <br style="clear:both">
            <div id="map_canvas"></div>
            <br />
            <div id="gmap"></div>
        </div>
        <div id="stream-frm-img" class="stream-frm-area" style="display: none;">
            <label>
                Images</label>
            <label class="fileinput-button">
                <input type="file" name="userfile0" size"78">
            </label>
            <input type="button" id="addFld" name="addField" value="Add more images" />
        </div>
        <div id="stream-frm-video" class="stream-frm-area" style="display: none;">
            <p>
                <label>
                    Video Source</label>
                <select id="stream-video-type" class="compose-form-input" option="test">
                    <option value="0">Choose Source</option>
                    <option value="youtube">YouTube</option>
                    <option value="vimeo">Vimeo</option>
                    <option value="dailymotion">Dailymotion</option>
                    <option value="bliptv">Blip.tv</option>
                    <option value="hulu">Hulu</option>
                    <option value="viddler">Viddler</option>
                    <option value="qik">QIK</option>
                    <option value="wordpresstv">Wordpress.tv</option>
                </select>
            </p>
            <p>
                <label>
                    Video URL</label>
                <input type="text" id="stream-video-url" class="compose-form-input" size="78">
            </p>
        </div>
        <div id="stream-frm-link" class="stream-frm-area" style="display: none;">
            <div>
                <div>
                    <label>
                        Link</label>
                    <input id="link_url" type="text" name="link_url" size="78">
                </div>
                <div>
                    <label>
                        Title</label>
                    <input type="text" id="link_title" name="link_title" size="78">
                </div>
                <div>
                    <label>
                        Description</label>
                    <textarea id="link_desc" name="link_desc" cols="60"></textarea>
                </div>                <div>
                    <label>
                        Upload Image</label>
                    <input type="file" id="link_img" name="link_image" size="60">
                </div>
                <div>
                    <label>
                        or Choose one</label>
                    <input type="hidden" name="link_img_url" id="frm_link_img" value="" />
                    <h4>Preview</h4>
                    <div id="img_selected"></div>
                    <hr>
                    <div id="img_chooser"></div>
                </div>
            </div>
        </div>
        <br clear="all" />
        <a id="shareButton" style="float:right; margin-top:7px; margin-right:7px;" class="fbut active">Share</a>
    </div>
</form>
<br clear="all" />
<div id="posting">
    <?php if (isset($streams) && !empty($streams)):
        ?>

        <?php foreach ($streams AS $stream):
            ?>
            <!-- STREAM MAIN -->
            <div class="psc-entry" id="record-<?php echo $stream->id ?>">
                <img src="<?php echo gravatar($stream->email, 35, 'x', true);
            ?>" style="float:left;" alt="" />
                <label style="float:left" class="name">
                    <?php if ($stream->object_type != 'friendship_confirm'):
                        ?>
                        <b><a href="http://127.0.0.1/pyro/pyrosocial/members/view/<?php echo $stream->user_id ?>"><?php echo $stream->full_name; ?></a></b>
                    <?php endif; ?>
                    <em><?php echo $stream->body; ?></em>
                    <abbr class="timeago" title="<? echo standard_date('DATE_ISO8601', $stream->created_on);
                    ?>"><?php echo timespan($stream->created_on); ?></abbr>
                    <span class="stream_like" data-id="<?php echo $stream->id; ?>">Like (<?php echo $stream->liked; ?>)</span>
                    <span class="stream_dislike" data-id="<?php echo $stream->id; ?>">Dislike (<?php echo $stream->disliked; ?>)</span>
                </label>
                <?php if ($stream->user_id == $this->current_user->id):
                    ?>
                    <a href="#" class="delete">X</a>
                <?php endif; ?>
                <br clear="both" />
                <div id="CommentPosted<?php echo $stream->id; ?>" class="psc-comments">
                    <?php
                    if (!empty($stream->recent_comments)):
                        $comments_row = unserialize($stream->recent_comments);
                        if ($stream->num_comments > 3)
                        {

                            echo '<div class="commentPanel" align="left"><a href="#" class="show_all_comments" data-id="' . $stream->id . '" data-count="' . $stream->num_comments . '">Show all (' . $stream->num_comments . ') comments</a></div>';
                        }
//echo 'count: '. (!empty($comments_row['count'])) ? $comments_row['count']:0;
//dump($comments_row);
                        foreach ($comments_row as $cid => $rows)
                        {
                            ?>
                            <div class="commentPanel" id="record-<?php echo $cid; ?>" align="left">
                                <img src="<?php echo gravatar($rows['email'], 25, 'x', true);
                            ?>" style="float:left;" class="CommentImg" alt="" />
                                <span style="float:left" class="name"> <b><a href="http://127.0.0.1/pyro/pyrosocial/members/view/<?php echo $rows['user_id'] ?>"><?php echo $rows['username']; ?></a></b></span>
                                <p class="postedComments">
                                    <?php echo $rows['body']; ?>
                                </p>
                                <br clear="all" />
                                <span class="timeago" title="<? echo standard_date('DATE_ISO8601', $rows['created_on']);
                                    ?>" style="margin-left:43px;"> <?php echo timespan($rows['created_on']); ?></span>
                                      <?php if ($this->current_user->id == $rows['user_id']):
                                          ?>
                                    &nbsp;&nbsp;<a href="#" id="CID-<?php echo $cid; ?>" class="c_delete">Delete</a>
                                <?php endif; ?>
                            </div>
                        <?php } ?>
                    <?php endif; ?>
                </div>
                <div class="commentBox" align="right" id="commentBox-<?php echo $stream->id ?>">
                    <img src="<?php echo gravatar($this->current_user->email, 25, 'x', true);
                    ?>" class="CommentImg" style="float:left;" alt="" />
                    <label id="record-<?php echo $stream->id ?>" class="c-label">
                        <textarea class="commentMark" id="commentMark-<?php echo $stream->id ?>" name="commentMark" cols="60"></textarea>
                    </label>
                    <!--<br clear="all" />
                    <a id="SubmitComment" class="small button comment"> Comment</a>//-->
                </div>
            </div>
            <br clear="both" />
        <?php endforeach; ?>

    <?php endif; ?>
</div>
<input type="button" value="Load More" class="button load_more">

<script type="text/javascript">
    jQuery(function($) {
        $('a[rel*=popbox]').facebox({
            loadingImage : '{{ asset:image_url file="module::loading.gif" }}',
            closeImage   : '{{ asset:image_url file="module::closelabel.png" }}'
        });
        $.fn.diload = function(options) {
            var settings = {
                threshold    : 0,
                failurelimit : 0,
                events       :"scroll",
                effect       : "show",
                container    : window
            };

            if(options) {
                $.extend(settings, options);
            }

            /* Fire one scroll event per scroll. Not one scroll event per image. */
            var elements = this;

            return this.each(function() {
                var self = this;

                /* Save original only if it is not defined in HTML. */
                if (undefined == $(self).attr("original")) {
                    self.loaded = false;
                    $(self).attr("original", $(self).attr("src"));
                }

                if (settings.placeholder) {
                    $(self).attr("src", settings.placeholder);
                } else {
                    $(self).removeAttr("src");
                }

                /* When appear is triggered load original image.
        $(self).click(function() {
        if (!this.loaded) {
        $(self)
        .hide()
        .attr("src", $(self).attr("original"))
        [settings.effect](settings.effectspeed);
        self.loaded = true;
        })
        .attr("src", $(self).attr("original"));
        };
        });*/

            });

            /* Force initial check if images should appear. */
            //$(settings.container).trigger(settings.event);

            return this;

        };
    });
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

    jQuery().ready(function($){
        $('.stream-vid-obj:hidden iframe').diload();
        $('.stream-vid-obj object embed').diload();
        $('.stream-vid-img').live('click',function(){
            var _vid = $(this).next('.stream-vid-obj');
            $(this).fadeOut();
            _vid.fadeIn();
            _objv = _vid.find('object');
            _obji = _vid.find('iframe');

            if (_objv)
            {
                _em = _objv.find('embed');
                _em.attr({'src': _em.attr('original')});
                _objv.replaceWith(_objv.clone());
                //_objv.remove();
            }

            if (_obji)
            {
                var _clone = _obji.attr({'src': $(self).attr('original')}).clone();
                _obji.remove();
            }

            _clone.insertAfter(this);

            //$(this).remove();
            return false;
        });

        $("#watermark").Watermark("What's on your mind?");
        $(".commentMark").Watermark("Write a comment...");

        $("#watermark").Watermark("watermark","#369");
        $(".commentMark").Watermark("watermark","#EEEEEE");
        $('textarea').elastic();

        function format_protocol(val){
            if(!/^(https?|ftp):\/\//i.test(val)) {
                val = 'http://'+val; // set both the value
            }
            return val;
        }
        function is_valid_url(val) {
            if (val.length == 0) return false;
            return /^(https?|ftp):\/\/(((([a-z]|\d|-|\.|_|~|[\u00A0-\uD7FF\uF900-\uFDCF\uFDF0-\uFFEF])|(%[\da-f]{2})|[!\$&amp;'\(\)\*\+,;=]|:)*@)?(((\d|[1-9]\d|1\d\d|2[0-4]\d|25[0-5])\.(\d|[1-9]\d|1\d\d|2[0-4]\d|25[0-5])\.(\d|[1-9]\d|1\d\d|2[0-4]\d|25[0-5])\.(\d|[1-9]\d|1\d\d|2[0-4]\d|25[0-5]))|((([a-z]|\d|[\u00A0-\uD7FF\uF900-\uFDCF\uFDF0-\uFFEF])|(([a-z]|\d|[\u00A0-\uD7FF\uF900-\uFDCF\uFDF0-\uFFEF])([a-z]|\d|-|\.|_|~|[\u00A0-\uD7FF\uF900-\uFDCF\uFDF0-\uFFEF])*([a-z]|\d|[\u00A0-\uD7FF\uF900-\uFDCF\uFDF0-\uFFEF])))\.)+(([a-z]|[\u00A0-\uD7FF\uF900-\uFDCF\uFDF0-\uFFEF])|(([a-z]|[\u00A0-\uD7FF\uF900-\uFDCF\uFDF0-\uFFEF])([a-z]|\d|-|\.|_|~|[\u00A0-\uD7FF\uF900-\uFDCF\uFDF0-\uFFEF])*([a-z]|[\u00A0-\uD7FF\uF900-\uFDCF\uFDF0-\uFFEF])))\.?)(:\d*)?)(\/((([a-z]|\d|-|\.|_|~|[\u00A0-\uD7FF\uF900-\uFDCF\uFDF0-\uFFEF])|(%[\da-f]{2})|[!\$&amp;'\(\)\*\+,;=]|:|@)+(\/(([a-z]|\d|-|\.|_|~|[\u00A0-\uD7FF\uF900-\uFDCF\uFDF0-\uFFEF])|(%[\da-f]{2})|[!\$&amp;'\(\)\*\+,;=]|:|@)*)*)?)?(\?((([a-z]|\d|-|\.|_|~|[\u00A0-\uD7FF\uF900-\uFDCF\uFDF0-\uFFEF])|(%[\da-f]{2})|[!\$&amp;'\(\)\*\+,;=]|:|@)|[\uE000-\uF8FF]|\/|\?)*)?(\#((([a-z]|\d|-|\.|_|~|[\u00A0-\uD7FF\uF900-\uFDCF\uFDF0-\uFFEF])|(%[\da-f]{2})|[!\$&amp;'\(\)\*\+,;=]|:|@)|\/|\?)*)?$/i.test(val);
            }

            /********** Image-Uploads ******************************************/
            var fileFlds = $('.fileinput-button');
            var i = fileFlds.size();

            $('#addFld').live('click', function() {
                upTpl = $('<label class="fileinput-button"><input type="file" name="userfile'+i+'"><input type="button" class="remFld" value="remove" /></label>');

                //clonedEl = fileFlds.insertAfter.append();
                upTpl.insertBefore('#addFld');
                i++;
                return false;
            });

            $('.remFld').live('click', function() {
                if( i > 1 ) {
                    $(this).parent('.fileinput-button').remove();
                    i--;
                }
                return false;
            });

            /********** Update Pretty Date ******************************************/
            $(".timeago").prettyDate();
            setInterval(function(){ $(".timeago").prettyDate(); }, 5000);

            /********** Main Tabs Navigation ************************************************/
            //On Click Event
            $(".stype span").click(function() {
                $(".stype span").removeClass("active"); //Remove any "active" class
                $(this).addClass("active"); //Add "active" class to selected tab
                var _stype = $(this).attr('data-filter');
                $(":input", ".stream-frm-area").attr('disabled', 'disabled');
                $(".stream-frm-area").hide(); //Hide all tab content

                var activeTab = $("#stream-frm-"+_stype);
                $(activeTab).fadeIn(); //Fade in the active content

                $(':input', activeTab).removeAttr('disabled');

                return false;
            });

            //Default Action
            $(".stream-frm-area").hide(); //Hide all content
            $(".stype span:first").trigger('click');

            var _busy=false;

            var requestUpdateData = {
                type: 'post',
                url: 'pyrosocial/update',
                data:{},
                dataType: 'json',
                success: function(response) {

                    //{"user_id":"1","body":false,"stream_type":"update","object_type":"user","object_id":0,"created_on":"6 Seconds","ip_address":2130706433,"is_active":1,"id":56,"images":[36,38,39],"full_name":"ryun shofner","avatar":"http:\/\/www.gravatar.com\/avatar\/9404c9c05dc1ffa15b285f0eb5613ddc?s=25&amp;r=x","created_iso8601":"2011-09-08T16:41:26Q"}
                    if (response.error){
                        var _errors='';
                        for (i in response.error){
                            _errors += '<div class="error-box">'+response.error[i]+'</div>';
                        }
                        //$.noticeAdd({text:_errors, type:'error'});
                        $(".UIComposer_Box").prepend(_errors);
                    }else {
                        str = '<div id="record-'+response.id+'" class="psc-entry"><img alt="" style="float:left; width:25px;height:25px;" src="'+response.avatar+'">';
                        str += '<label class="name" style="float:left"><b><a href="pyrosocial/members/view/'+response.user_id+'">'+response.full_name+'</a></b>';
                        str += '<em>';
                        if (typeof response.images != 'undefined' && response.images.length > 0){
                            for (i in response.images)
                            {
                                str += '<a href="uploads/pyrosocial/main/'+response.user_id+'/'+response.images[i]+'.jpg" class="" target="_blank" rel="popbox"><img src="uploads/pyrosocial/thumbs/'+response.user_id+'/'+response.images[i]+'.jpg"></a>';
                            }
                        }
                        //str += $('<div/>').hide().append($(response.body).css('display','none')).html();
                        str += response.body;
                        str += '</em><br clear="all"> posted <span class="timeago" title="'+response.created_iso8601+'">'+response.created_on+'</span> &nbsp; ';
                        str += '<a class="stream_like" data-id="'+response.id+'">Like (0)</a><a class="stream_dislike" data-id="'+response.id+'">Dislike (0)</a></label><a class="delete" href="#" style="display: none;">X</a>';
                        str += '<br clear="all" />';
                        str += '<div id="CommentPosted'+response.id+'" class="psc-comments"></div>';
                        str += '<div class="commentBox" align="right" id="commentBox-'+response.id+'">';
                        str += '<img src="'+response.avatar+'" class="CommentImg" style="float:left;" alt="" />';
                        str += '<label id="record-'+response.id+'"  class="c-label">';
                        str += '<textarea class="commentMark" id="commentMark-'+response.id+'" name="commentMark" cols="60"></textarea>';
                        str += '</label>';
                        str += '<br clear="all" />';
                        str += '</div>';
                        str += '</div>';
                        str += '<br clear="both" />';

                        requestUpdateData.data={};
                        // Create Template for response
                        $('#frmStream').clearForm();
                        $('#posting').prepend(str).fadeIn('slow');
                        $("#sbody").val("What's on your mind?");
                    }
                    _busy.busy('hide');
                }
            };

            /********** Links - Retrieve remote meta data and images ******************************************/
            $('#link_url').focusout(function(e){
                var link_url = format_protocol($(this).val());
                if (is_valid_url(link_url))
                {
                    _busy = $('.UIComposer_Box').busy();
                    $.getJSON("pyrosocial/scrape_url",{'url':link_url}, function(r){
                        $("#link_title").val(r.title);
                        $("#link_desc").val(r.desc);
                        var imgSelect = $('<ul />');
                        for(i in r.img)
                        {
                            imgSelect.append('<li>'+r.img[i].html+'</li>');
                        }
                        $('li', imgSelect).click(function(){
                            $('#link_img').attr('disabled', 'disabled');
                            $('li.sel', imgSelect).removeClass('sel');
                            $(this).addClass('sel');
                            var _img = $('img' ,this).clone();
                            $('#img_selected').html(_img);
                            $('#frm_link_img').val(_img.attr('src'));

                        });
                        $('#img_chooser').html(imgSelect);
                        _busy.busy('hide');
                    });

                }
            });

            /********* Switch from static image to video ******************************/
            /*$('.stream-vid-img').live('click',function(){
        $(this).fadeOut().next('.stream-vid-obj').fadeIn();
        });*/

            /********* Main Submit for streams form ******************************/
            $('#shareButton').live('click',function(){
                var _type = $('.stype span.active').attr('data-filter');
                //$('.UIComposer_Box .error-box').remove();
                if (_type == 'video')
                {
                    requestUpdateData.data.oetype = _type;
                    requestUpdateData.data.oeprovider = $('#stream-video-type :selected').val();
                    requestUpdateData.data.oeurl = $('#stream-video-url').val();
                }
                _busy = $('.UIComposer_Box').busy();
                $('#frmStream').ajaxSubmit(requestUpdateData);

                return false;
            });

            /******* Post Comment **************************************************/
            $('.commentMark').live('keypress', function(e) {
                if(e.which == 13){
                    var getpID =  $(this).parent().attr('id').replace('record-','');
                    var comment_text = $("#commentMark-"+getpID).val();

                    if(comment_text != "Write a comment...")
                    {
                        _busy = $(this).busy({img : '<?php echo site_url($this->module_details['path']); ?>/img/busy.gif'});
                        $.post("pyrosocial/comment", {'comment':comment_text,"stream_id":getpID}, function(response){
                            var str ='';
                            console.log(response.comments);
                            for (i in response.comments)
                            {
                                str += '<div align="left" id="record-'+i+'" class="commentPanel"><img alt="" style="float:left; width:25px;height:25px;" src="'+response.comments[i].avatar+'">';
                                str += '<span class="name" style="float:left"><b><a href="http://127.0.0.1/pyro/pyrosocial/members/view/'+response.comments[i].user_id+'">'+response.comments[i].username+'</a></b></span>';
                                str += '<p class="postedComments">'+response.comments[i].body+'</p><br clear="all"><span class="timeago" title="'+response.comments[i].created_iso860+'" style="margin-left:43px; color:#666666; font-size:11px">'+response.comments[i].created_iso860+'</span>';
                                if (response.comments[i].is_author)
                                {
                                    str += '&nbsp;&nbsp;<a href="#" id="CID-'+i+'" class="c_delete">Delete</a>';
                                }
                                str += '</div>';
                            }
                            // Create Template for response
                            //$('#posting').prepend(str).fadeIn('slow');
                            _busy.busy('hide');

                            $('#CommentPosted'+getpID).fadeOut().html(str).fadeIn('slow');
                            $("#commentMark-"+getpID).val("Write a comment...");
                        }, 'json');
                    }
                    return false;
                }
            });

            /********* Show more comments ******************************/
            $('.show_all_comments').live("click", function(e){

                var sid =  $(this).attr('data-id');
                var ccount =  $(this).attr('data-count');
                $.post("pyrosocial/show_comments/"+sid+"/"+ccount,{}, function(response){
                    var str ='';
                    for (i in response.comments)
                    {
                        str += '<div align="left" id="record-'+i+'" class="commentPanel"><img alt="" style="float:left; width:25px;height:25px;" src="'+response.comments[i].avatar+'">';
                        str += '<span class="name" style="float:left"><b><a href="http://127.0.0.1/pyro/pyrosocial/members/view/'+response.comments[i].user_id+'">'+response.comments[i].username+'</a></b></span>';
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
            /******* Show More Streams **************************************************/
            var _post_count = 0;
            $('input.load_more').click(function(e){
                var self = this;
                //var media_path = $('#media_path').val();
                _post_count = (_post_count+10);
                $.ajax({
                    type: "GET",
                    dataType: "html",
                    url: "pyrosocial/index/"+ _post_count,
                    success: function(r){
                        if (r.length < 1)
                        {
                            $(self).val('Nothing else to load..').attr('disable', 'disable');
                            return false;
                        }
                        //alert(msg.msg);
                        $('#posting').append(r);
                    }
                });
            });
            /********* Stream like or unlike ******************************/
            $(".stream_like, .stream_dislike").live('click',function(){
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
                    me.addClass('stream_dislike');
                }
                else {
                    _req.action = 'dislike';
                    me.removeClass('stream_dislike');
                    me.addClass('stream_like');
                }

                _req.ltype = (is_comment) ? 1:0;

                //fadeout the vote-count
                //$("span#votes_count"+the_id).fadeOut("fast");

                //the main ajax request
                $.post('pyrosocial/new_like', _req, function(msg) {
                    me.html('unlike');

                    //fadein the vote count
                    //$("span#votes_count"+the_id).fadeIn();
                    //remove the spinner
                    //$("span#vote_buttons"+the_id).remove();
                });
            });

            /********* Remove Comment ******************************/
            $('a.c_delete').live("click", function(e){

                if(confirm('Are you sure you want to delete this comment?')==false)
                    return false;

                e.preventDefault();
                var c_id =  $(this).attr('id').replace('CID-','');
                var s_id =  $(this).closest('.psc-comments').attr('id').replace('CommentPosted','');

                var parent  = $('#record-' + c_id);

                $.ajax({
                    type: 'get',
                    url: 'pyrosocial/del_comment/'+ c_id,
                    data: {'stream_id':s_id},
                    success: function(){
                        parent.fadeOut(200,function(){ parent.remove();});
                    }
                });
                return true;
            });

            /// hover show remove button
            $('.psc-entry').live("mouseenter", function(e){
                $("a.delete", this).show();
            });
            $('.psc-entry').live("mouseleave", function(e){
                $("a.delete", this).hide();
            });
            /// hover show remove button

            /********* Remove Stream ******************************/
            $('a.delete').live("click", function(e){
                if(confirm('Are you sure you want to delete this post?')==false){
                    return false;
                }

                e.preventDefault();
                var id = $(this).closest('.psc-entry').attr('id');
                var parent  = $('#' + id);
                var temp    = id.replace('record-','');

                $.ajax({
                    type: 'get',
                    url: 'pyrosocial/del_stream/'+ parent.attr('id').replace('record-',''),
                    data: '',
                    success: function(){
                        parent.fadeOut(200,function(){parent.remove();});
                    }
                });
            });

        });
</script>
