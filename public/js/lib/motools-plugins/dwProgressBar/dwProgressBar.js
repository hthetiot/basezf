/*
	Class:    	dwProgressBar
	Author:   	David Walsh
	Website:    http://davidwalsh.name
	Version:  	2.0
	Date:     	08/03/2008
	Built For:  MooTools 1.2.0

	SAMPLE USAGE AT BOTTOM OF THIS FILE

*/


//class is in
var dwProgressBar = new Class({

	//implements
	Implements: [Events, Options],

	//options
	options: {
		container: $(document.body),
		boxID:'',
		percentageID:'',
		displayID:'',
		startPercentage: 0,
		displayText: false,
		speed:10,
		step:1,
		allowMore: false
	},

	//initialization
	initialize: function(options) {
		//set options
		this.setOptions(options);
		//create elements
		this.createElements();
	},

	//creates the box and percentage elements
	createElements: function() {
		var box = new Element('div', {
			id:this.options.boxID
		});
		var perc = new Element('div', {
			id:this.options.percentageID,
			'style':'width:0px;'
		});
		perc.inject(box);
		box.inject(this.options.container);
		if(this.options.displayText) {
			var text = new Element('div', {
				id:this.options.displayID
			});
			text.inject(this.options.container);
		}
		this.set(this.options.startPercentage);
	},

	//calculates width in pixels from percentage
	calculate: function(percentage) {
		return ($(this.options.boxID).getStyle('width').replace('px','') * (percentage / 100)).toInt();
	},

	//animates the change in percentage
	animate: function(go) {
		var run = false;
		if(!this.options.allowMore && go > 100) {
			go = 100;
		}
		this.to = go.toInt();
		$(this.options.percentageID).set('morph', {
			duration: this.options.speed,
			link:'cancel',
			onComplete: this.fireEvent(go == 100 ? 'complete' : 'change', [], this.options.speed)
		}).morph({
			width:this.calculate(go)
		});
		if(this.options.displayText) {
			$(this.options.displayID).set('text', this.to + '%');
		}
	},

	//sets the percentage from its current state to desired percentage
	set: function(to) {
		this.animate(to);
	},

	//steps a pre-determined percentage
	step: function() {
		this.set(this.to + this.options.step);
	}

});

/* sample usage */
/*
	//once the DOM is ready
	window.addEvent('domready', function() {

		//create the progress bar for example 1
		pb2 = new dwProgressBar({
			container: $('put-bar-here2'),
			startPercentage: 10,
			speed:1000,
			boxID: 'box2',
			percentageID: 'perc2',
			displayID: 'text',
			displayText: true,
			step:15,
			onComplete: function() {
				alert('Done!');
			},
			onChange: function() {
				alert('Changed!');
			}
		});

		//movers
		$$('.mover2').each(function(el) {
			el.addEvent('click',function(e) {
				e.stop();
				pb2.set(el.get('rel'));
			});
		});

		//steppers
		$$('.stepper').each(function(el) {
			el.addEvent('click',function(e) {
				e.stop();
				pb.step();
			});
		});

*/

