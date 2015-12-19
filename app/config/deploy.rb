set :application, "bestfootball"
set :domain,      "bestfootball.ezwebcreation.fr"
set :deploy_to,   "/var/www/bestfootball"
set :app_path,    "app"

set :repository,  "https://github.com/jojotjebaby/Bestfootball"
set :scm,         :git
set :deploy_via,  :copy
# Or: `accurev`, `bzr`, `cvs`, `darcs`, `subversion`, `mercurial`, `perforce`, or `none`

set :model_manager, "doctrine"
# Or: `propel`

role :web,        domain                         # Your HTTP server, Apache/etc
role :app,        domain, :primary => true       # This may be the same as your `Web` server

set  :keep_releases,  3

set :shared_children,     [app_path + "/logs", web_path + "/uploads", "vendor"]
set :use_composer, true
set :update_vendors, true

default_run_options[:pty] = true

set :clear_controllers, false

# Be more verbose by uncommenting the following line
logger.level = Logger::MAX_LEVEL
