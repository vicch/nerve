<!-- <pre style="font-size:12px;margin-left:10px;"><?php echo var_export($data) ?></pre> -->
<canvas id="nerve-map" width="1045" height="950" style="opacity:1;"></canvas>
<script>
(function($){

    // var Renderer = function(canvas){
    //     var canvas = $(canvas).get(0)
    //     var ctx = canvas.getContext("2d");
    //     var particleSystem

    //     var that = {
    //         init:function(system){
    //             //
    //             // the particle system will call the init function once, right before the
    //             // first frame is to be drawn. it's a good place to set up the canvas and
    //             // to pass the canvas size to the particle system
    //             //
    //             // save a reference to the particle system for use in the .redraw() loop
    //             particleSystem = system

    //             // inform the system of the screen dimensions so it can map coords for us.
    //             // if the canvas is ever resized, screenSize should be called again with
    //             // the new dimensions
    //             particleSystem.screenSize(canvas.width, canvas.height) 
    //             particleSystem.screenPadding(80) // leave an extra 80px of whitespace per side
                
    //             // set up some event handlers to allow for node-dragging
    //             that.initMouseHandling()
    //         },
            
    //         redraw:function(){
    //             // 
    //             // redraw will be called repeatedly during the run whenever the node positions
    //             // change. the new positions for the nodes can be accessed by looking at the
    //             // .p attribute of a given node. however the p.x & p.y values are in the coordinates
    //             // of the particle system rather than the screen. you can either map them to
    //             // the screen yourself, or use the convenience iterators .eachNode (and .eachEdge)
    //             // which allow you to step through the actual node objects but also pass an
    //             // x,y point in the screen's coordinate system
    //             // 
    //             // ctx.fillStyle = "white"
    //             var background = new Image();
    //             background.src = "img/dot_bg.jpg";
    //             var pattern = ctx.createPattern(background, "repeat");
    //             ctx.fillStyle = pattern;
    //             ctx.fillRect(0,0, canvas.width, canvas.height)
                
    //             particleSystem.eachEdge(function(edge, pt1, pt2){
    //                 // edge: {source:Node, target:Node, length:#, data:{}}
    //                 // pt1:    {x:#, y:#}    source position in screen coords
    //                 // pt2:    {x:#, y:#}    target position in screen coords

    //                 // draw a line from pt1 to pt2
    //                 ctx.strokeStyle = "rgba(0,0,0, .333)"
    //                 ctx.lineWidth = 1
    //                 ctx.beginPath()
    //                 ctx.moveTo(pt1.x, pt1.y)
    //                 ctx.lineTo(pt2.x, pt2.y)
    //                 ctx.stroke()
    //             })

    //             particleSystem.eachNode(function(node, pt){
    //                 // node: {mass:#, p:{x,y}, name:"", data:{}}
    //                 // pt:     {x:#, y:#}    node position in screen coords

    //                 // draw a rectangle centered at pt
    //                 var w = 10
    //                 ctx.fillStyle = (node.data.alone) ? "orange" : "black"
    //                 ctx.fillRect(pt.x-w/2, pt.y-w/2, w,w)
    //             })                    
    //         },
            
    //         initMouseHandling:function(){
    //             // no-nonsense drag and drop (thanks springy.js)
    //             var dragged = null;

    //             // set up a handler object that will initially listen for mousedowns then
    //             // for moves and mouseups while dragging
    //             var handler = {
    //                 clicked:function(e){
    //                     var pos = $(canvas).offset();
    //                     _mouseP = arbor.Point(e.pageX-pos.left, e.pageY-pos.top)
    //                     dragged = particleSystem.nearest(_mouseP);

    //                     if (dragged && dragged.node !== null){
    //                         // while we're dragging, don't let physics move the node
    //                         dragged.node.fixed = true
    //                     }

    //                     $(canvas).bind('mousemove', handler.dragged)
    //                     $(window).bind('mouseup', handler.dropped)

    //                     return false
    //                 },
    //                 dragged:function(e){
    //                     var pos = $(canvas).offset();
    //                     var s = arbor.Point(e.pageX-pos.left, e.pageY-pos.top)

    //                     if (dragged && dragged.node !== null){
    //                         var p = particleSystem.fromScreen(s)
    //                         dragged.node.p = p
    //                     }

    //                     return false
    //                 },

    //                 dropped:function(e){
    //                     if (dragged===null || dragged.node===undefined) return
    //                     if (dragged.node !== null) dragged.node.fixed = false
    //                     dragged.node.tempMass = 1000
    //                     dragged = null
    //                     $(canvas).unbind('mousemove', handler.dragged)
    //                     $(window).unbind('mouseup', handler.dropped)
    //                     _mouseP = null
    //                     return false
    //                 }
    //             }
                
    //             // start listening
    //             $(canvas).mousedown(handler.clicked);

    //         },
            
    //     }
    //     return that
    // }

    var Renderer = function(elt){
        var dom = $(elt)
        var canvas = dom.get(0)
        var ctx = canvas.getContext("2d");
        var gfx = arbor.Graphics(canvas)
        var sys = null

        var _vignette = null
        var selected = null,
                nearest = null,
                _mouseP = null;

        
        var that = {
            init:function(pSystem){
                sys = pSystem
                sys.screen({size:{width:dom.width(), height:dom.height()},
                                        padding:[36,60,36,60]})

                $(window).resize(that.resize)
                that.resize()
                that._initMouseHandling()

                if (document.referrer.match(/echolalia|atlas|halfviz/)){
                    // if we got here by hitting the back button in one of the demos, 
                    // start with the demos section pre-selected
                    that.switchSection('demos')
                }
            },
            resize:function(){
                // canvas.width = $(window).width()
                // canvas.height = .75* $(window).height()
                // sys.screen({size:{width:canvas.width, height:canvas.height}})
                _vignette = null
                that.redraw()
            },
            redraw:function(){
                gfx.clear()
                sys.eachEdge(function(edge, p1, p2){
                    if (edge.source.data.alpha * edge.target.data.alpha == 0) return
                    gfx.line(p1, p2, {stroke:edge.target.data.color, width:4, alpha:edge.target.data.alpha})
                })
                sys.eachNode(function(node, pt){
                    if (node.data.color=='red') {
                        var w = Math.max(60, 80+gfx.textWidth(node.name))
                    } else {
                        var w = Math.max(40, 60+gfx.textWidth(node.name))
                    }
                    if (node.data.alpha===0) return
                    gfx.oval(pt.x-w/2, pt.y-w/2, w, w, {fill:node.data.color, alpha:node.data.alpha})
                    gfx.text(node.name, pt.x, pt.y+7, {color:"white", align:"center", font:"Asap", size:17})
                    gfx.text(node.name, pt.x, pt.y+7, {color:"white", align:"center", font:"Asap", size:17})
                })
                // that._drawVignette()
            },
            
            _drawVignette:function(){
                var w = canvas.width
                var h = canvas.height
                var r = 20

                if (!_vignette){
                    var top = ctx.createLinearGradient(0,0,0,r)
                    top.addColorStop(0, "#e0e0e0")
                    top.addColorStop(.7, "rgba(255,255,255,0)")

                    var bot = ctx.createLinearGradient(0,h-r,0,h)
                    bot.addColorStop(0, "rgba(255,255,255,0)")
                    bot.addColorStop(1, "white")

                    _vignette = {top:top, bot:bot}
                }
                
                // top
                ctx.fillStyle = _vignette.top
                ctx.fillRect(0,0, w,r)

                // bot
                ctx.fillStyle = _vignette.bot
                ctx.fillRect(0,h-r, w,r)
            },

            switchMode:function(e){
                if (e.mode=='hidden'){
                    dom.stop(true).fadeTo(e.dt,0, function(){
                        if (sys) sys.stop()
                        $(this).hide()
                    })
                }else if (e.mode=='visible'){
                    dom.stop(true).css('opacity',0).show().fadeTo(e.dt,1,function(){
                        that.resize()
                    })
                    if (sys) sys.start()
                }
            },
            
            switchSection:function(newSection){
                var parent = sys.getEdgesFrom(newSection)[0].source
                var children = $.map(sys.getEdgesFrom(newSection), function(edge){
                    return edge.target
                })
                
                sys.eachNode(function(node){
                    if (node.data.shape=='dot') return // skip all but leafnodes

                    var nowVisible = ($.inArray(node, children)>=0)
                    var newAlpha = (nowVisible) ? 1 : 0
                    var dt = (nowVisible) ? .5 : .5
                    sys.tweenNode(node, dt, {alpha:newAlpha})

                    if (newAlpha==1){
                        node.p.x = parent.p.x + .05*Math.random() - .025
                        node.p.y = parent.p.y + .05*Math.random() - .025
                        node.tempMass = .001
                    }
                })
            },
            
            
            _initMouseHandling:function(){
                // no-nonsense drag and drop (thanks springy.js)
                selected = null;
                nearest = null;
                var dragged = null;
                var oldmass = 1

                var _section = null

                var handler = {

                    moved:function(e){
                        var pos = $(canvas).offset();
                        _mouseP = arbor.Point(e.pageX-pos.left, e.pageY-pos.top)
                        nearest = sys.nearest(_mouseP);

                        if (!nearest.node) return false

                        // if (nearest.node.data.shape!='dot'){
                            selected = (nearest.distance < 50) ? nearest : null
                            if (selected && selected.node.data.link){
                                e.target.style.cursor = 'pointer'
                                dom.addClass('linkable')
                                // window.status = selected.node.data.link.replace(/^\//,"http://"+window.location.host+"/").replace(/^#/,'')
                            }
                            else{
                                e.target.style.cursor = 'default'
                                dom.removeClass('linkable')
                                // window.status = ''
                            }
                        // }else if ($.inArray(nearest.node.name, ['arbor.js','code','docs','demos']) >=0 ){
                        //     if (nearest.node.name!=_section){
                        //         _section = nearest.node.name
                        //         that.switchSection(_section)
                        //     }
                        //     dom.removeClass('linkable')
                        //     window.status = ''
                        // }
                        
                        return false
                    },

                    clicked:function(e){
                        var pos = $(canvas).offset();
                        _mouseP = arbor.Point(e.pageX-pos.left, e.pageY-pos.top)
                        nearest = dragged = sys.nearest(_mouseP);
                        
                        if (dragged && dragged.node !== null) dragged.node.fixed = true

                        $(canvas).unbind('mousemove', handler.moved);
                        $(canvas).bind('mousemove', handler.dragged)
                        $(window).bind('mouseup', handler.dropped)

                        return false
                    },

                    dblclicked:function(e){
                        var pos = $(canvas).offset();
                        _mouseP = arbor.Point(e.pageX-pos.left, e.pageY-pos.top)
                        nearest = dragged = sys.nearest(_mouseP);
                        
                        // if (nearest && selected && nearest.node===selected.node){
                            var link = selected.node.data.link
                            if (link){
                            //      $(that).trigger({type:"navigate", path:link.substr(1)})
                            // }else{
                                    window.location = link
                            }
                            return false
                        // }
                        
                        
                        if (dragged && dragged.node !== null) dragged.node.fixed = true

                        $(canvas).unbind('mousemove', handler.moved);
                        $(canvas).bind('mousemove', handler.dragged)
                        $(window).bind('mouseup', handler.dropped)

                        return false
                    },

                    dragged:function(e){
                        var old_nearest = nearest && nearest.node._id
                        var pos = $(canvas).offset();
                        var s = arbor.Point(e.pageX-pos.left, e.pageY-pos.top)

                        if (!nearest) return
                        if (dragged !== null && dragged.node !== null){
                            var p = sys.fromScreen(s)
                            dragged.node.p = p
                        }

                        return false
                    },

                    dropped:function(e){
                        if (dragged===null || dragged.node===undefined) return
                        if (dragged.node !== null) dragged.node.fixed = false
                        dragged.node.tempMass = 1000
                        dragged = null;
                        // selected = null
                        $(canvas).unbind('mousemove', handler.dragged)
                        $(window).unbind('mouseup', handler.dropped)
                        $(canvas).bind('mousemove', handler.moved);
                        _mouseP = null
                        return false
                    }

                }

                $(canvas).mousedown(handler.clicked);
                $(canvas).dblclick(handler.dblclicked);
                $(canvas).mousemove(handler.moved);

            }
        }
        
        return that
    }
    
    var Nav = function(elt){
        var dom = $(elt)
    
        var _path = null
        
        var that = {
            init:function(){
                $(window).bind('popstate',that.navigate)
                dom.find('> a').click(that.back)
                $('.more').one('click',that.more)
                
                $('#docs dl:not(.datastructure) dt').click(that.reveal)
                that.update()
                return that
            },
            more:function(e){
                $(this).removeAttr('href').addClass('less').html('&nbsp;').siblings().fadeIn()
                $(this).next('h2').find('a').one('click', that.less)
                
                return false
            },
            less:function(e){
                var more = $(this).closest('h2').prev('a')
                $(this).closest('h2').prev('a')
                .nextAll().fadeOut(function(){
                    $(more).text('creation & use').removeClass('less').attr('href','#')
                })
                $(this).closest('h2').prev('a').one('click',that.more)
                
                return false
            },
            reveal:function(e){
                $(this).next('dd').fadeToggle('fast')
                return false
            },
            back:function(){
                _path = "/"
                if (window.history && window.history.pushState){
                    window.history.pushState({path:_path}, "", _path);
                }
                that.update()
                return false
            },
            navigate:function(e){
                var oldpath = _path
                if (e.type=='navigate'){
                    _path = e.path
                    if (window.history && window.history.pushState){
                         window.history.pushState({path:_path}, "", _path);
                    }else{
                        that.update()
                    }
                }else if (e.type=='popstate'){
                    var state = e.originalEvent.state || {}
                    _path = state.path || window.location.pathname.replace(/^\//,'')
                }
                if (_path != oldpath) that.update()
            },
            update:function(){
                var dt = 'fast'
                if (_path===null){
                    // this is the original page load. don't animate anything just jump
                    // to the proper state
                    _path = window.location.pathname.replace(/^\//,'')
                    dt = 0
                    dom.find('p').css('opacity',0).show().fadeTo('slow',1)
                }
    
                switch (_path){
                    case '':
                    case '/':
                    dom.find('p').text('a graph visualization library using web workers and jQuery')
                    dom.find('> a').removeClass('active').attr('href','#')
    
                    $('#docs').fadeTo('fast',0, function(){
                        $(this).hide()
                        $(that).trigger({type:'mode', mode:'visible', dt:dt})
                    })
                    document.title = "arbor.js"
                    break
                    
                    case 'introduction':
                    case 'reference':
                    $(that).trigger({type:'mode', mode:'hidden', dt:dt})
                    dom.find('> p').text(_path)
                    dom.find('> a').addClass('active').attr('href','#')
                    $('#docs').stop(true).css({opacity:0}).show().delay(333).fadeTo('fast',1)
                                        
                    $('#docs').find(">div").hide()
                    $('#docs').find('#'+_path).show()
                    document.title = "arbor.js » " + _path
                    break
                }
                
            }
        }
        return that
    }

    // $(document).ready(function(){
    //     var sys = arbor.ParticleSystem(1000, 600, 0.5) // create the system with sensible repulsion/stiffness/friction
    //     sys.parameters({gravity:true}) // use center-gravity to make the graph settle nicely (ymmv)
    //     sys.renderer = Renderer("#nerve-map") // our newly created renderer will have its .init() method called shortly by sys...
        
    //     // add some nodes to the graph and watch it go...
    //     sys.addEdge('a','b')
    //     sys.addEdge('a','c')
    //     sys.addEdge('a','d')
    //     sys.addEdge('a','e')
    //     sys.addEdge('d','e')
    //     // sys.addNode('f', {alone:true, mass:.25})
        
    //     // or, equivalently:
    //     //
    //     // sys.graft({
    //     //     nodes:{
    //     //         f:{alone:true, mass:.25}
    //     //     }, 
    //     //     edges:{
    //     //         a:{ b:{},
    //     //                 c:{},
    //     //                 d:{},
    //     //                 e:{}
    //     //         }
    //     //     }
    //     // })
                
    // })
    
    // $(document).ready(function(){
    //     var CLR = {
    //         word_to:"#b2b19d",
    //         code:"orange",
    //         doc:"#922E00",
    //         demo:"#a7af00"
    //     }
    //     var theUI = {
    //         nodes:{
    //             "arbor.js":{color:"red", shape:"dot", alpha:1},
    //             demos:{color:CLR.branch, shape:"dot", alpha:1},
    //             halfviz:{color:CLR.demo, alpha:0, link:'/halfviz'},
    //             atlas:{color:CLR.demo, alpha:0, link:'/atlas'},
    //             echolalia:{color:CLR.demo, alpha:0, link:'/echolalia'},
    //             docs:{color:CLR.branch, shape:"dot", alpha:1},
    //             reference:{color:CLR.doc, alpha:0, link:'#reference'},
    //             introduction:{color:CLR.doc, alpha:0, link:'#introduction'},
    //             code:{color:CLR.branch, shape:"dot", alpha:1},
    //             github:{color:CLR.code, alpha:0, link:'https://github.com/samizdatco/arbor'},
    //             ".zip":{color:CLR.code, alpha:0, link:'/js/dist/arbor-v0.92.zip'},
    //             ".tar.gz":{color:CLR.code, alpha:0, link:'/js/dist/arbor-v0.92.tar.gz'}
    //         },
    //         edges:{
    //             "arbor.js":{
    //                 demos:{length:.8},
    //                 docs:{length:.8},
    //                 code:{length:.8}
    //             },
    //             demos:{
    //                 halfviz:{},
    //                 atlas:{},
    //                 echolalia:{}
    //             },
    //             docs:{
    //                 reference:{},
    //                 introduction:{}
    //             },
    //             code:{
    //                 ".zip":{},
    //                 ".tar.gz":{},
    //                 "github":{}
    //             }
    //         }
    //     }
    //     var sys = arbor.ParticleSystem()
    //     sys.parameters({stiffness:900, repulsion:2000, gravity:true, dt:0.015})
    //     sys.renderer = Renderer("#nerve-map")
    //     sys.graft(theUI)

    //     var nav = Nav("#nav")
    //     $(sys.renderer).bind('navigate', nav.navigate)
    //     $(nav).bind('mode', sys.renderer.switchMode)
    //     nav.init()
    // })

    $(document).ready(function(){
        var CLR = {
            synonym:"#2572EB",         // color 1 blue
            antonym:"#9E1716",         // color 2 brown
            verb_obj:"#128425",        // color 3 green
            obj_verb:"#128425",        // color 3 green
            subj_mod:"#128425",        // color 3 green
            mod_subj:"#128425",        // color 3 green
            diff:"#2572EB",            // color 1 blue
            association:"#008E8E",     // color 4 cyan
            translation:"#DE4AAD",     // color 5 pink
            sense_contain:"#7200AC",   // color 6 purple
            sense_belong:"#7200AC",    // color 6 purple
            literal_contain:"#128425", // color 3 green
            literal_belong:"#128425",  // color 3 green
            explain_by:"#008E8E",      // color 4 cyan
            explain:"#008E8E",         // color 4 cyan
        }
        var theUI = {
            nodes:{
                "<?php echo $data['word']['word'] ?>":{color:"red", shape:"dot", alpha:1},
                <?php foreach($data['relations'] as $relation): ?>
                "<?php echo $relation['word_to']['word'] ?>":{
                    color:CLR.<?php echo $relation['type']['type'] ?>,
                    link:'/nerve/?word=<?php echo $relation['word_to']['word'] ?>',
                    shape:"dot",
                    alpha:1
                },
                <?php endforeach; ?>
            },
            edges:{
                "<?php echo $data['word']['word'] ?>":{
                    <?php foreach($data['relations'] as $relation): ?>
                    "<?php echo $relation['word_to']['word'] ?>":{length:.8},
                    <?php endforeach; ?>
                },
            }
        }
        var sys = arbor.ParticleSystem()
        sys.parameters({stiffness:3000, repulsion:300, gravity:true, dt:0.015})
        sys.renderer = Renderer("#nerve-map")
        sys.graft(theUI)

        var nav = Nav("#nav")
        $(sys.renderer).bind('navigate', nav.navigate)
        $(nav).bind('mode', sys.renderer.switchMode)
        nav.init()
    })

})(this.jQuery)
</script>