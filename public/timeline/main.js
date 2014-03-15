$(document).ready(function() {
    var application = {
        intervalId: false,
        size: {width: 0, height: 0},
        source: null,
        dataRaw: null,
        data: null,
        initialize: function() {
            this.size.width = $(window).width() - 20;
            this.size.height = $(window).height() - 80;
        },
        update: function() {
            var self = this;

            var sourceAlt = this.source.split('?')[0].replace('Post/Published/', '');

            $.getJSON(sourceAlt, function(dataAlt) {
                $.getJSON(self.source, function(data) {
                    self.dataRaw = data;
                    self.dataRaw.main = dataAlt;
                    self.intervalId = window.setInterval(self._check, 5000);
                    self._process();
                    self.run();
                });
            });
        },
        _convertDate: function(date) {
            if(date){
               if(date.indexOf('-') !=-1){
                 date = '20'+date;
             }
         }
         console.log(date);

         date = new Date(date);
         var newDate = date.getFullYear() + ',' + date.getMonth() + ',' + date.getDate() + ',' + date.getHours() + ',' + date.getMinutes() + ',' + date.getSeconds();
         return newDate;
     },
     _process: function() {
        var special = ['twitter', 'flickr', 'soundcloud', 'instagram', 'youtube', 'facebook'];

        var data = {timeline: {
            type: 'default',
            headline: this.dataRaw.main.Title,
            text: this.dataRaw.main.Description,
            startDate: this._convertDate(this.dataRaw.main.CreatedOn),
            date: []
        }};

        var posts = this.dataRaw.PostList;
        posts.reverse();

        data.timeline.startDate = this._convertDate(posts[0].PublishedOn);

        for (var i = 0; i < posts.length; i = i + 1) {
            var post = posts[i];
            if (post.Meta !== undefined) {
                var meta = JSON.parse(post.Meta);
            } else {
                var meta = {};
            }
            var item = {
                headline: '',
                startDate: this._convertDate(post.PublishedOn),
                asset: {
                    media: '',
                    credit: '',
                    caption: ''
                }
            };
            if (post.Content) {
                if (post.Content.indexOf('<b>') !== -1) {
                    var el = $('<div></div>');
                    el.html(post.Content);
                    item.headline = $('b', el).html();
                    $('b', el).remove();
                    post.Content = el.html();
                    el.remove();
                } else {
                    if (special.indexOf(post.AuthorName) === -1) {
                        item.headline = post.AuthorName + ':';
                    }
                }
            }
            if (special.indexOf(post.AuthorName) === -1) {
                if (post.Content.indexOf('<img') !== -1) {
                    var el = $('<div></div>');
                    el.html(post.Content);
                    item.asset.media = $('img', el)[0].src;
                    $('img', el).remove();
                    post.Content = el.html();
                    el.remove();
                    if (meta.caption !== '') {
                        item.asset.caption = meta.caption;
                    }
                }
                if (meta.GsearchResultClass === 'GwebSearch') {
                    item.headline = meta.title;
                }
                item.text = post.Content;
            } else {
                item.text = '';
                if (meta.annotation.before !== null) {
                    item.text = meta.annotation.before + ' ';
                }

                if (post.AuthorName === 'twitter') {
                    var trimmedString = meta.text.substr(0, 50);
                    item.headline = trimmedString.substr(0, Math.min(trimmedString.length, trimmedString.lastIndexOf(" ")))+'...';
                    item.asset.media = 'http://twitter.com/' + meta.from_user + '/status/' + meta.id_str;
                } else if (post.AuthorName === 'flickr') {
                    item.asset.media = 'http://www.flickr.com/photos/' + meta.owner + '/' + meta.id + '/in/photostream/';
                    item.headline = meta.title;
                } else if (post.AuthorName === 'instagram') {
                    item.asset.media = meta.link;
                    item.asset.caption = meta.tags.join(' ');
                } else if (post.AuthorName === 'youtube') {
                    item.headline = meta.title;
                    item.asset.media = 'http://youtu.be/' + meta.id;
                    item.asset.caption = meta.description;
                } else if (post.AuthorName === 'soundcloud') {
                    item.headline = meta.title;
                    item.asset.media = meta.permalink_url;
                    item.asset.caption = meta.description;
                } else if (post.AuthorName === 'facebook') {
                    item.headline = meta.from.name + ':';
                    item.text = item.text + meta.message;
                }

                if (meta.annotation.after !== null) {
                    item.text = item.text + ' ' + meta.annotation.after;
                }
            }
            data.timeline.date.push(item);
        }

        this.data = data;
    },
    _check: function() {
        var self = this;
        var newPosts = 0;
        $.getJSON(application.source, function(data) {
            newPosts = parseInt(data.total, 10) - parseInt(application.dataRaw.total, 10);
            if (newPosts > 0) {
                $('#info').html('There are ' + newPosts + ' new posts!');
                $('#info').show();
            } else {
                $('#info').hide();
            }
        });
    },
    run: function() {
        $('#timeline').html('');

        createStoryJS({
            type:       'timeline',
            width:      this.size.width,
            height:     this.size.height,
            source:     this.data,
            embed_id:   'timeline',
            start_at_end: true
        });
    }
};

    /*
    $(window).resize(function() {
        application.initialize();
    });
*/

application.initialize();

    //

    $('#form').on('submit', function(event) {
        event.preventDefault();
        var source = $('#source').val();
        if (source !== '') {
            window.clearInterval(application.intervalId);
            application.source = source;
            application.update();
        }
    });

    //

    $('#source').val('http://mobile.sd-demo.sourcefabric.org/resources/LiveDesk/Blog/8/Post/Published.json?X-Filter=*&limit=1000');
    application.source = $('#source').val();
    application.update();
});