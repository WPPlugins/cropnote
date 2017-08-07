(function($) {
	$.cropnote = {};
	var cropnote =		'cropnote', classes = {
		canvas: 		'cropnote-canvas',
		view: 			'cropnote-view',
		loading: 		'cropnote-loading',
		background:		'cropnote-background',
		edit:			'cropnote-edit',
		bgImage:		'cropnote-background-image',
		add:			'cropnote-add',
		ok:				'cropnote-ok',
		close:			'cropnote-close',
		area:			'cropnote-area',
		editable:		'cropnote-area-editable',
		note:			'cropnote-note',
		areaHover:		'cropnote-area-hover',
		editableHover:	'cropnote-area-editable-hover',
		editDelete:		'cropnote-edit-delete'
	}
	var methods = {
		init: function(options) {
			var opts = $.extend(true, {}, $.cropnote.defaults, options);
			var image = this;
			
			//save state on this object
			$(this).data(cropnote, {
				imageloaded: false,
				image: this,
				mode: 'view',
				options: opts,
				canvas: $('<div class="' + classes.canvas + '"><div class="' + classes.view + ' ' + classes.loading + '"></div><div class="' + classes.background + '"></div><div class="' + classes.edit + '"></div></div>'),
				notes: opts.notes			
			})
			var data = $(this).data(cropnote);
			
			//add the note canvas to the DOM
			var canvas = data.canvas;
	        canvas.children('.' + classes.edit).hide();
	        canvas.children('.' + classes.view).hide();
	        image.after(canvas);
	        
	        //style the canvas
	   		canvas.height(this.height());
	        canvas.width(this.width());
			var bgImage = $('<img src="' + this.attr('src') + '" class="' + classes.bgImage + '">');
		    canvas.children('.' + classes.background).append(bgImage); 
			canvas.children('.' + classes.view, '.' + classes.edit, '.' + classes.background).height(this.height()).width(this.width());
	        
	        //hide/show notes when the mouse is hovering over the picture
	        var showNoteCanvas = function() {
	            if ($(this).children('.' + classes.edit).css('display') == 'none') {
	                $(this).children('.' + classes.view).show();
	            }
	        };
	        var hideNoteCanvas = function() {
	        	if(data.jcropDisabled) {
	        		disableJcrop(image);
	        	}
	            if($(this).children().hasClass(classes.error) || $(this).children().hasClass(classes.loading)) {
	            	$(this).children('.' + classes.view).show();
				} else {
					$(this).children('.' + classes.view).hide();
				}
	        };
	        var showNotes = function() {
				if($(this).hasClass(classes.error)) {
					$(this).show();	
					$(this).removeClass(classes.error);
					$(this).addClass(classes.loading);
					ajaxLoad(this);
				} else {
					$(this).show();	
				}
	       	};
	       	var hideNotes = function(e) {
				if($(this).hasClass(classes.error) || $(this).hasClass(classes.loading)) {
	            	$(this).show();
				} else {
					$(this).hide();
					if(opts.hover) reshowBoxesIfInRange(canvas, e.pageX, e.pageY);
				}
		    };
	        if(opts.hover) {
	        	canvas.hover(showNoteCanvas, hideNoteCanvas);
		        canvas.children('.' + classes.view).hover(showNotes, hideNotes);
		    }
		    else {
		    	var notesOn = false;
		    	canvas.on('click.cropnote', function(e) {
		    		if(notesOn) {
		    			notesOn = false;
		    			hideNoteCanvas.call(canvas);
		    			hideNotes.call(canvas.children('.' + classes.view), e);
		    		}
		    		else {
		    			notesOn = true;
		    			showNoteCanvas.call(canvas);
		    			showNotes.call(canvas.children('.' + classes.view));
		    		}
		    	});
	        }
		    
		    //load the notes with ajax or synchronously, depending on the option useAjax
		    if(opts.useAjax) {
		    	ajaxLoad(this);
		    }
		    else {
		    	load(this);
		    }
		    
		    //add additional markup to the image - add note, set options, etc.
		    var topbar = $('<div style="width:' + (this.width() + 10) + 'px"></div>');
		    if(opts.addable) {
				var button = $('<a class="' + classes.add + '" id="image-annotate-add" href="#' + opts.getImgID.substring(4, opts.getImgID.length) + '">Add Note</a>');
	            button.click(function() {
			        if (data.mode == 'view') {
			            data.mode = 'edit';
			
			            // Create/prepare the editable note elements
			            var editable = new annotateEdit(image);
			
			            createSaveButton(editable, image);
			            createCancelButton(editable, image);
			        }
	            });
	            topbar.append(button);
		    }
		    
		    if(opts.showActionMessage) {
		    	var message = $('<span class="cropnote-desc">' + opts.actionMessage + '</span>');
		    	topbar.prepend(message);
		    }
		    
		    if(opts.showActionMessage || opts.addable) {
	            canvas.before(topbar);
		    }
		    
		    //hide the original image (we've created a copy of the image)
		    this.hide();
	
			return this;
		},
		clear: function() {
			var data = $(this).data(cropnote);
			for(var i = 0; i < data.notes.length; i++) {
				data.notes[i].destroy();
			}
			data.notes = [];
			return this;
		},
		add: function(text, note, callback, form) {
			var data = $(this).data(cropnote);
			if (note.area) {
				note.resetPosition(text, this);
			} else {
				note.editable = true;
				note = new annotateView(this, note)
				note.resetPosition(text, this);
				data.notes.push(note);
			}
			if(!form) {
				form = $('<form></form>');
			}
			appendPosition(form, note.note, this);
			data.mode = 'view';
		
			// Save via AJAX
			if (data.options.useAjax) {
				$.ajax({
					url: data.options.pluginUrl + "?action=cropnote&cropnote_action=save&imgid=" + data.options.getImgID + "&postid=" + data.options.getPostID,
					data: form.serialize(),
					error: function(xhr, ajaxOptions, thrownError) { /*alert("An error occured saving that note." + thrownError)*/ },
					success: function(jsonData) {
						if (jsonData.annotation_id != undefined) {
							note.id = data.options.annotation_id;
						}
						data.options.afterAjax(jsonData, data);
					},
					dataType: "json",
					beforeSend: function(jqXHR, settings) { data.options.beforeAjax(jqXHR, settings, data); },
					type: data.options.ajaxMethod
				});
			}
			if(callback) { callback(note); } //return note so it can be edited or deleted later by an api call
			return this;
		},
		remove: function(note) {
			var data = $(this).data(cropnote);
			for(var i = 0; i < data.notes.length; i++) {
				if(note == data.notes[i]) {
					var close = $('.' + classes.close);
					if(close) { close.click(); } //close note editor TODO: might be overkill, may only need to do this if we're deleting the specific note we're editing...
					data.notes.splice(i, 1);
					note.destroy();
					break;
				}
			}
		},
		edit: function(note, options) {
			var data = $(this).data(cropnote);
			for(var i = 0; i < data.notes.length; i++) {
				if(note == data.notes[i]) {
					var close = $('.' + classes.close);
					if(close) { close.click(); } //close note editor TODO: might be overkill, may only need to do this if we're deleting the specific note we're editing...
					this.cropnote('add', options.text, $.extend(note, options), null, null);
					break;
				}
			}
		}
	};
	
	//cropnote entry function.  call the requested method if registered.  call init if method isn't specified
	$.fn.cropnote = function(method) {
		if ( methods[method] ) {
			return methods[ method ].apply( this, Array.prototype.slice.call( arguments, 1 ));
		} else if ( typeof method === 'object' || ! method ) {
			return methods.init.apply( this, arguments );
		} else {
			$.error( 'Method ' +  method + ' does not exist on jQuery.cropnote' );
		}    
	};
	
	//handle asynchronous loading of notes
	function ajaxLoad(image) {
		var data = $(image).data(cropnote);
		data.ajaxLoadTime = setTimeout(function() { ajaxTimeOut(image) }, data.options.ajaxTimeout);
		$.ajax({
			url: data.options.pluginUrl + '?action=cropnote&cropnote_action=get&imgid=' + data.options.getImgID + '&ticks=' + new Date().getTime(),
			error: function(xhr, ajaxOptions, thrownError) { 
				/*alert("An error occured saving that note." + thrownError)*/ },
			success: function(jsonData) {
        		data.notes = jsonData.notes; //TODO: should this append notes to existing ones so we can mix ajax and static notes?
        		load(image);
        		data.options.afterAjax(jsonData, data);
        	},
			dataType: 'json',
			beforeSend: function(jqXHR, settings) { data.options.beforeAjax(jqXHR, settings, data); },
			type: data.options.ajaxMethod
		});
		
	}
	
	//load the notes
	function load(image) {
		var data = $(image).data(cropnote);
		data.canvas.children('.' + classes.view).removeClass(classes.loading);
		data.imageloaded = true;
		//image.canvas.children('.image-annotate-view').hide();
		
		data.canvas.children('.' + classes.background).children('img').Jcrop({
			minSize: [10, 10]
		}, function() {
			data.jcrop = this;
			disableJcrop(image);
		});
		if(data.notes.length != 0) {
			for (var i = 0; i < data.notes.length; i++) {
				data.notes[i] = new annotateView(image, data.notes[i]);
			}
		}
	}
	
	function createSaveButton(editable, image, note) {
        var ok = $('<a class="' + classes.ok + '">OK</a>');
        var data = $(image).data(cropnote);

        ok.click(function() {
            var form = $('#image-annotate-edit-form form');
            var text = $('#image-annotate-text').val();
			var author = $('#noteauthor').val();
			var email = $('#noteemail').val();
			
			author = author == undefined ? "" : author
			email = email == undefined ? "" : email
			
			var check = false;
			
			if(text != "") {
				if(data.editable == false) {
					if(author != "" && email !="") {
						AtPos = email.indexOf("@")
						StopPos = email.lastIndexOf(".")
						
						if (AtPos == -1 || StopPos == -1) {
							$("#errormsg").html('<span style="color:#C00">Please enter a valid email.</span>');	
						} else {
							check = true;
						}
					} else {
						$("#errormsg").html('<span style="color:#C00">Please fill the required fields (name, email).</span>');	
					}
				} else {
					check = true
				}
			} else {
				$("#errormsg").html('<span style="color:#C00">Please type a note.</span>');	
			}
			
			if(check == true) {
				$(image).cropnote('add', text, editable.note ? editable.note : note, null, form);
			
				disableJcrop(image);
				editable.destroy();
			}
        });
        editable.form.append(ok);
	}
	
	function createCancelButton(editable, image) {
        var data = $(image).data(cropnote), jcrop = data.jcrop;
        var cancel = $('<a class="' + classes.close + '">Cancel</a>');
        cancel.click(function() {
            editable.destroy();
            disableJcrop(image);
            data.mode = 'view';
        });
        editable.form.append(cancel);
	}
	
	//disable Jcrop, hide its selection line, and move its tracker to the back for displaying notes (and so right click can save the image)
    function disableJcrop(image) {
        var data = $(image).data(cropnote), jcrop = data.jcrop;
        jcrop.release();
        jcrop.disable();
        jcrop.setOptions(data.options.jcrop_view);
        $('.' + classes.background + ' .jcrop-hline, .' + classes.background +  ' .jcrop-vline').hide();
        data.jcropTrackerZ = $('.' + classes.background + ' .jcrop-tracker').css('zIndex');
        $('.' + classes.background + ' .jcrop-tracker').css('zIndex', 0);
        data.jcropDisabled = true;
    }
    
    //handles scaling positions and sizes of the notes based on the current dimensions of the image and the dimensions when the note was created
    function scaleDimension(savedDimension, originalImageDimension, newImageDimension) {
        if(!originalImageDimension) return savedDimension;
		else return savedDimension / originalImageDimension * newImageDimension;
    };

	//creates an editable note area
	function annotateEdit(image, note) {
		var data = $(image).data(cropnote);
        this.image = image;

        if (note) {
            this.note = note;
        } else {
        	this.note = {
        		id: 'new',
        		top: 30,
        		left: 30,
        		width: 130,
        		height: 80,
        		text: '',
        		imageHeight: image.height(),
        		imageWidth: image.width()
        	};
        }

        // Show the edition canvas and hide the view canvas
        data.canvas.children('.cropnote-view').hide();
        data.canvas.children('.cropnote-edit').show();
		
		/*//filter note
		for(var i = 0; i<this.note.text.length; i++) {
			var str = this.note.text
			if(str.substring(i,i+6) == "<br />") {
				this.note.text = str.substring(0,i);
			}
		}*/
		
        // Add the note (which we'll load with the form afterwards)
		if(data.editable) {
			var form = $('<div id="image-annotate-edit-form" style="height:100px;"><form><textarea id="image-annotate-text" name="text" rows="3" cols="30" maxlength="' + data.options.maxLength + '">' + this.note.text + '</textarea></form><div id="errormsg">You can start edit the note here.</div></div>');
		} else {
        	var form = $('<div id="image-annotate-edit-form"><form><label for="author">Name : </label><input name="author" id="noteauthor" type="text" maxlength="1000" /><br /><label for="email" >Email : </label><input name="email" id="noteemail" type="text" maxlength="100" /><textarea id="image-annotate-text" name="text" rows="3" cols="30" maxlength="' + data.options.maxLength + '">' + this.note.text + '</textarea></form><div id="errormsg">Fill in the require fields to submit.</div></div>');
			
		}
        this.form = form;
		var edit = this;
		var left = scaleDimension(parseInt(this.note.left), parseInt(this.note.imageWidth), image.width());
		var top = scaleDimension(parseInt(this.note.top), parseInt(this.note.imageHeight), image.height());
		var width = scaleDimension(parseInt(this.note.width), parseInt(this.note.imageWidth), image.width());
		var height = scaleDimension(parseInt(this.note.height), parseInt(this.note.imageHeight), image.height());
		var positionForm = function(c) {
			form.css({ top: c.y2 + 2, left: c.x });
		};
		data.jcrop.release();
		data.jcrop.enable();
		data.jcrop.setOptions($.extend(data.options.jcrop_edit, { onSelect: positionForm, onChange: positionForm, 
			setSelect: [ left, top, left + width, top + height] }));
        $('.' + classes.background + ' .jcrop-hline, .' + classes.background +  ' .jcrop-vline').show();
        $('.' + classes.background + ' .jcrop-tracker').css('zIndex', data.jcropTrackerZ);
		data.jcropDisabled = false;
		
        data.canvas.append(this.form);
        this.form.css({left: left, top: top + height + 2 });
		
		$('textarea[maxlength]').keyup(function() {
			var max = parseInt($(this).attr('maxlength'));
			if($(this).val().length > max){
				$(this).val($(this).val().substr(0, $(this).attr('maxlength')));
				$("#errormsg").html('<span style="color:#C00">You have ' + (max - $(this).val().length) + ' characters remaining</span>');
			} else {
				$("#errormsg").html('You have ' + (max - $(this).val().length) + ' characters remaining');	
			}
		});
		
        return this;
	}
	
	annotateEdit.prototype.destroy = function() {
		$(this.image).data(cropnote).canvas.children('.' + classes.edit).hide();
		this.form.remove();
	}
	
	//creates a note area
	function annotateView(image, note) {
		this.image = image;
		this.note = note;
		var data = $(image).data(cropnote);
		
		this.editable = note.editable && data.options.editable;
		
        // Add the area
        this.area = $('<div class="' + classes.area + (this.editable ? ' ' + classes.editable : '') + '"><div class="' + classes.background + '"></div><div></div></div>');
        data.canvas.children('.' + classes.view).prepend(this.area);

        // Add the note
		this.form = $('<div class="' + classes.note + '">' + note.text + '</div>');
        this.form.hide();
        data.canvas.children('.' + classes.view).append(this.form);
        this.form.children('span.actions').hide();
		
		if(this.note.height > this.note.width) {
			this.area.css('z-index', 10020 - (Math.round(this.note.height/100 * 1)))
			this.form.css('z-index', 10020 - (Math.round(this.note.height/100 * 1)))
		} else {
			this.area.css('z-index', 10020 - (Math.round(this.note.width/100 * 1)))
			this.form.css('z-index', 10020 - (Math.round(this.note.height/100 * 1)))
		}
		
        // Set the position and size of the note
        this.setPosition();

        // Add the behavior: hide/display the note when hovering the area
        var annotation = this;
        var showNote = function() {
			var data = $(image).data(cropnote);
			for(var i = 0; i < data.notes.length; i++) {
				$(data.notes[i]).data('notesOn', false);
				data.notes[i].hide();
			}
            annotation.show();
            if(data.jcrop && data.jcropDisabled) {
            	data.jcrop.setSelect([
            		scaleDimension(note.left, note.imageWidth, image.width() + 2),
            		scaleDimension(note.top, note.imageHeight, image.height() + 2),
            		scaleDimension(note.left + note.width, note.imageWidth, image.width() - 1),
            		scaleDimension(note.top + note.height, note.imageHeight, image.height())]);
            }
        };
        var hideNote = function() {
            annotation.hide();
            if(data.jcrop && data.jcropDisabled) {
            	data.jcrop.release();
            }
		};
        if(data.options.hover) {
	        this.area.hover(showNote, hideNote);
        }
        else {
        	$(annotation).data('notesOn', false);
        	this.area.on('click.cropnote', function(e) {
        		e.stopPropagation(); //stop propagation so that the canvas doesn't see this event and hide all notes
        		if($(annotation).data('notesOn')) {
        			$(annotation).data('notesOn', false);
        			hideNote();
        		}
        		else {
        			showNote();
        			$(annotation).data('notesOn', true);
        		}
        	});
        }

		var cl = data.options.hover ? 'click' : 'dblclick';
        // Edit a note feature
        if (this.editable) {
            var form = this;
            this.area[cl](function() {
                form.edit();
            });
        } else {
			this.area[cl](function() {
				window.location.hash = "#comment-" + note.commentid;
			});
		}
	}
	
	//sets the note's position
	annotateView.prototype.setPosition = function() {
        var height = (scaleDimension(parseInt(this.note.height), parseInt(this.note.imageHeight), this.image.height()) - 2);
        var width = (scaleDimension(parseInt(this.note.width), parseInt(this.note.imageWidth), this.image.width()) - 2);
        var left = (scaleDimension(parseInt(this.note.left), parseInt(this.note.imageWidth), this.image.width()));
        var top = (scaleDimension(parseInt(this.note.top), parseInt(this.note.imageHeight), this.image.height()));
        this.area.children('div').height(height + 'px');
        this.area.children('div').width(width + 'px');
        this.area.css('left', left + 'px');
        this.area.css('top', top + 'px');
        //try to position the note in a smart way - move the note's left position if the note is at the far right, move the note to the top if the note is at the bottom
        var mwidth = parseInt(this.form.css('max-width'));
        if(this.image.width() - left < mwidth) {
        	left = Math.max(this.image.width() - mwidth, 0);
        }
        this.form.css('left', left + 'px');
        var ftop = (scaleDimension((parseInt(this.note.top) + parseInt(this.note.height) + 7), parseInt(this.note.imageHeight), this.image.height()));
        var fheight = this.form.height();
        if(this.image.height() - ftop < fheight) {
        	if(this.image.height() - ftop < top - 21) { //only move to top if there is more room
        		ftop = Math.max(top - fheight - 21, 0);
        	}
        }
        this.form.css('top', ftop + 'px');
	}
	
	//highlight the note area and show the note text
	annotateView.prototype.show = function() {
        if(this.form.oldindex == undefined) {
			this.form.oldindex = this.form.css("z-index");
		}
		this.form.css('z-index', 10020);
        this.form.fadeIn(250);
        if (!this.editable) {
            this.area.addClass(classes.areaHover);
        } else {
            this.area.addClass(classes.editableHover);
        }
	}

	//hide the note
	annotateView.prototype.hide = function() {
        this.form.fadeOut(250);
		this.form.css('z-index', this.form.oldindex);
        this.area.removeClass(classes.areaHover);
        this.area.removeClass(classes.editableHover);
	}
	
	annotateView.prototype.destroy = function() {
		this.area.remove();
		this.form.remove();
	}
	
	annotateView.prototype.edit = function() {
		var data = $(this.image).data(cropnote);
        if (data.mode == 'view') {
            data.mode = 'edit';
            var annotation = this;

            // Create/prepare the editable note elements
            var editable = new annotateEdit(this.image, this.note);

            createSaveButton(editable, this.image, annotation);

            // Add the delete button
            var del = $('<a class="' + classes.editDelete + '">Delete</a>');
            del.click(function() {
				var data = $(annotation.image).data(cropnote);
                var form = $('#image-annotate-edit-form form');
				
				appendPosition(form, editable.note, annotation.image)
                if (data.options.useAjax) {
                    $.ajax({
                        url: data.options.pluginUrl + "?action=cropnote&cropnote_action=delete&imgid=" + data.options.getImgID,
                        data: form.serialize(),
                        error: function(e) { alert("An error occured deleting that note.") },
                        success: function(jsonData) { data.options.afterAjax(jsonData, data); },
                        beforeSend: function(jqXHR, settings) { data.options.beforeAjax(jqXHR, settings, data); },
                        dataType: 'json',
                        type: data.options.ajaxType
                    });
                }

                data.options.mode = 'view';
                editable.destroy();
                disableJcrop(annotation.image);
                annotation.destroy();
            });
            editable.form.append(del);

            createCancelButton(editable, this.image);
        }
	}
	
	function appendPosition(form, note, image) {
		var data = $(image).data(cropnote);
        var sel = data.jcrop.tellScaled();
        var areaFields = $('<input type="hidden" value="' + sel.h + '" name="height"/>' +
                           '<input type="hidden" value="' + sel.w + '" name="width"/>' +
                           '<input type="hidden" value="' + sel.y + '" name="top"/>' +
                           '<input type="hidden" value="' + sel.x + '" name="left"/>' +
                           '<input type="hidden" value="' + image.width() + '" name="imageWidth"/>' +
                           '<input type="hidden" value="' + image.height() + '" name="imageHeight"/>' +
                           '<input type="hidden" value="' + note.id + '" name="id"/>');
        form.append(areaFields);
	}
	
	annotateView.prototype.resetPosition = function(text, image) {
		var data = $(image).data(cropnote);
        this.form.html(text);
        this.form.hide();

        var sel = data.jcrop.tellScaled();
        // Resize
        this.area.children('div').height(sel.h + 'px');
        this.area.children('div').width((sel.w - 2) + 'px');
        this.area.css('top', sel.y);
        this.area.css('left', sel.x);
        this.form.css('left', (sel.x) + 'px');
        this.form.css('top', (sel.y2 + 7) + 'px');

        // Save new position to note
        this.note.top = sel.y;
        this.note.left = sel.x;
        this.note.height = sel.h;
        this.note.width = sel.w;
        this.note.imageHeight = image.height();
        this.note.imageWidth = image.width();
        this.note.text = text;
        this.editable = true;
	}
	
	//IE hack
    function reshowBoxesIfInRange(canvas, x, y) {
    	var offset = canvas.offset();
    	if(offset.top <= y && y <= offset.top + canvas.height() && offset.left <= x && x <= offset.left + canvas.width()) {
    		canvas.children('.' + classes.view).show();
    	}
    }
	
	//display error condition if ajax fails to load
	function ajaxTimeOut(image) {
		var data = $(image).data(cropnote);
		if(data && data.imageloaded == false) {
			data.canvas.children('.' + classes.view).removeClass(classes.loading).addClass(classes.error).show();
		}
	}

	//Plugin global defaults
	$.cropnote.defaults = {
        pluginUrl: 'cropnote-ajax.php',
        editable: true,
        useAjax: true,
        notes: [],
        hover: true,			//hover is good for PCs, but doesn't work well for mobile devices
        ajaxTimeout: 15000,		//15 second timeout for loading notes
        maxLength: 140,			//140 character limit for notes
        jcrop_view: {			//jcrop styling for view mode
        	allowSelect: false,
        	allowMove: false,
        	allowResize: false,
        	createHandles: [],
        	createDragbars: [],
        	createBorders: [],
        	drawBorders: false,
        	dragEdges: false, 
        	onSelect: function() {}, 
        	onChange: function() {},
        	bgColor: 'white',
        	bgOpacity: 0.5,
        	addClass: 'jcrop-light'
        },
        jcrop_edit: {			//jcrop styling for add/edit mode
			allowSelect: $.Jcrop.defaults.allowSelect,
        	allowMove: $.Jcrop.defaults.allowMove,
        	allowResize: $.Jcrop.defaults.allowResize,
        	createHandles: $.Jcrop.defaults.createHandles,
        	createDragbars: $.Jcrop.defaults.createDragbars,
        	createBorders: $.Jcrop.defaults.createBorders,
        	drawBorders: $.Jcrop.defaults.drawBorders,
        	dragEdges: $.Jcrop.defaults.dragEdges
		},
		showActionMessage: false,
		actionMessage: 'Click image to view notes',
		beforeAjax: function(jqXHR, settings, data) {
			return true;			
		},
		afterAjax: function(jsonData, data) {},
		ajaxMethod: 'get'
	};
})(jQuery);
