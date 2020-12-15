module.exports = function(grunt) {
    
        grunt.registerTask('watch', [ 'watch' ]);
    
        grunt.initConfig({
            pkg: grunt.file.readJSON('package.json'),
    
            // Uglify.
            uglify: {
                options: {
                    mangle: false
                },
                js: {
                    files: {
                        'assets/js/admin-scripts.min.js': ['assets/js/admin-scripts.js']
                    }
                }
            },

            // Watch our project for changes.
            watch: {
                // JS
                js: {
                    files: ['assets/js/admin-scripts.js'],
                    tasks: ['uglify:js'],
                }
            }
    
        });
    
        // Saves having to declare each dependency
        require( "matchdep" ).filterDev( "grunt-*" ).forEach( grunt.loadNpmTasks );
    
        grunt.registerTask('default', [ 'uglify' ]);
    };
    