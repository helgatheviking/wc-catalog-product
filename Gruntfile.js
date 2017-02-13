module.exports = function(grunt) {

  require('load-grunt-tasks')(grunt);
	
  // Project configuration.
  grunt.initConfig({
	pkg: grunt.file.readJSON('package.json'),

	// compile 
	sass: {                              // Task
		dev: {                            // Target
			options: {                       // Target options
				style: 'expanded'
			},
			files: {                         // Dictionary of files
				'assets/css/wc-catalog-product-metabox.css': 'assets/scss/wc-catalog-product-metabox.scss',       // 'destination': 'source'
				'assets/css/wc-catalog-product-frontend.css': 'assets/scss/wc-catalog-product-frontend.scss'
			}
		},
		prod: {                            // Target
			options: {                       // Target options
				style: 'compact',
				sourcemap: 'none'
			},
			files: {                         // Dictionary of files
				'assets/css/wc-catalog-product-metabox.css': 'assets/scss/wc-catalog-product-metabox.scss',       // 'destination': 'source'
				'assets/css/wc-catalog-product-frontend.css': 'assets/scss/wc-catalog-product-frontend.scss'
			}
		}
	},

	uglify: {
		options: {
			compress: {
				global_defs: {
					"EO_SCRIPT_DEBUG": false
				},
				dead_code: true
				},
			banner: '/*! <%= pkg.title %> <%= pkg.version %> <%= grunt.template.today("yyyy-mm-dd HH:MM") %> */\n'
		},
		build: {
			files: [{
				expand: true,	// Enable dynamic expansion.
				src: [ 'assets/js/*.js', '!assets/js/*.min.js' ], // Actual pattern(s) to match.
				ext: '.min.js',   // Dest filepaths will have this extension.
			}]
		}
	},
	jshint: {
		options: {
			reporter: require('jshint-stylish'),
			globals: {
				"EO_SCRIPT_DEBUG": false,
			},
			 '-W099': true, //Mixed spaces and tabs
			 '-W083': true,//TODO Fix functions within loop
			 '-W082': true, //Todo Function declarations should not be placed in blocks
			 '-W020': true, //Read only - error when assigning EO_SCRIPT_DEBUG a value.
		},
		all: [ 'js/*.js', '!js/*.min.js' ]
  	},

	watch: {
		scripts: {
			files: 'assets/js/*.js',
			tasks: ['uglify'],
			options: {
				debounceDelay: 250,
			},
		},
		css: {
			files: 'assets/scss/*.scss',
			tasks: ['sass:dev'],
		},
	},

	// # docs
	wp_readme_to_markdown: {
		convert:{
			files: {
				'readme.md': 'readme.txt'
			},
		},
	},

	// # Internationalization 

	// Add text domain
	addtextdomain: {
		options: {
            textdomain: '<%= pkg.name %>',    // Project text domain.
            updateDomains: [ 'wc-catalog-product' ]  // List of text domains to replace.
        },
		target: {
			files: {
				src: ['*.php', 'includes/**/*.php', 'includes/admin/*.php', '!node_modules/**', '!build/**', '!assets/vendor/composer/**', '!assets/vendor/bower/**']
			}
		}
	},

	// Generate .pot file
	makepot: {
		target: {
			options: {
				domainPath: '/languages', // Where to save the POT file.
				exclude: ['build/.*', 'vendor/.*', 'node_modules/.*', 'languages/.*', 'assets/bower/.*', 'assets/composer/.*'], // List of files or directories to ignore.
				mainFile: '<%= pkg.name %>.php', // Main project file.
				potFilename: '<%= pkg.name %>.pot', // Name of the POT file.
				type: 'wp-plugin' // Type of project (wp-plugin or wp-theme).
			}
		}
	},

	// bump version numbers
	replace: {
		Version: {
			src: [
				'readme.txt',
				'<%= pkg.name %>.php'
			],
			overwrite: true,
			replacements: [
				{
					from: /Stable tag:.*$/m,
					to: "Stable tag: <%= pkg.version %>"
				},
				{ 
					from: /Version:.*$/m,
					to: "Version: <%= pkg.version %>"
				},
				{ 
					from: /public \$version = \'.*.'/m,
					to: "public $version = '<%= pkg.version %>'"
				}
			]
		}
	},

	clean: {
		//Clean up build folder
		main: ['build/']
	},

	copy: {
		// Copy the plugin to a versioned release directory
		main: {
			expand: true,
		    src: [
		    	'**',
		    	'!*~',
				'!node_modules/**',
				'!.sass-cache/**',
				'!build/**',
				'!bower.json',
				'!*.map',
				'!.git/**','!.gitignore','!.gitmodules',
				'!tests/**',
				'!Gruntfile.js','!package.json',
				'!composer.lock','!composer.phar','!composer.json','!auth.json',
				'!CONTRIBUTING.md',
				'!gitcreds.json',
				'!.gitignore',
				'!.gitmodules',
				'!*~',
				'!*.sublime-workspace',
				'!*.sublime-project',
				'!*.transifexrc',
				'!deploy.sh',
				'!languages/.tx',
				'!languages/tx.exe',
				'!README.md',
				'!wp-assets/**',
				'!*.zip'
		    ], 
		    dest: 'build/'
		  }  
	},

	// make a zipfile
	compress: {
	  main: {
	    options: {
	      archive: '<%= pkg.name %>.zip'
	    },
	    expand: true,
	    cwd: 'build/',
	    src: ['**/*'],
	    dest: '<%= pkg.name %>'
	  }
	}


});

grunt.registerTask( 'docs', [ 'wp_readme_to_markdown'] );

grunt.registerTask( 'test', [ 'jshint', 'newer:uglify' ] );

grunt.registerTask( 'build', [ 'replace', 'newer:uglify', 'addtextdomain', 'makepot', 'sass:prod', 'copy', 'compress' ] );

// bump version numbers 
// grunt release		1.4.1 -> 1.4.2
// grunt release:minor	1.4.1 -> 1.5.0
// grint release:major	1.4.1 -> 2.0.0

};
