set :application, "bestfootball.fr"
set :domain, "joris@bestfootball.fr" # Le SSH de destination
set :deploy_to, "/var/www/bestfootball.fr" # Le répertoire de destination
set :app_path, "app" # Le dossier d’application, laissez app
set :user, "joris" # Le nom d’utilisateur du serveur distant

set :repository, "git@github.com:jojotjebaby/Bestfootball.git" # L’URL de votre repository
set :branch, "master" # La branche Git, utile si vous pushez vos releases de prod sur une branche particulière
set :scm, :git # SVN ? Haha, vous plaisantez j’espère :-)
set :deploy_via, :copy # Ils y a plusieurs méthodes de déploiements, nous utilisons la méthode de copy

set :model_manager, "doctrine" # ORM

role :web, domain
role :app, domain, :primary => true

# Nous utilisons sudo pour régler les permissions via la methode :chown
# préférez l’utilisation des ACLs si c’est disponible sur votre serveur

set :use_sudo, true
set :keep_releases, 3 # Le nombre de releases à garder après un déploiement réussi

## Symfony2
set :shared_files, ["app/config/parameters.yml"] # Les fichiers à conserver entre chaque déploiement
set :shared_children, [app_path + "/logs", "vendor"] # Idem, mais pour les dossiers
set :use_composer, true
set :update_vendors, false # Il est conseillé de laisser a false et de ne pas faire de ‘composer update’ directement sur la prod
#set :composer_options, "--verbose --prefer-dist" # Permet de spécifier des paramètres supplémentaires à composer, inutile dans notre cas
set :writable_dirs, ["app/cache", "app/logs"] # Application des droits nécessaires en écriture sur les dossiers
set :webserver_user, "www-data" # L’utilisateur de votre serveur web (Apache, nginx, etc.)
set :permission_method, :chown # Dans le cas où vous n’avez pas les ACLs, ne pas oublier de mettre :use_sudo à true
set :use_set_permissions, true
set :dump_assetic_assets, true # dumper les assets

#default_run_options[:pty] = true # Si vous avez cette erreur : no tty present and no askpass program specified, alors décommentez
#ssh_options[:forward_agent] = true # Idem que ci-dessus

# Permet d’avoir le détail des logs de capistrano, plus facile à débugger si vous rencontrer des erreurs
logger.level = Logger::MAX_LEVEL
default_run_options[:pty] = true

# Et enfin, si jamais vous rencontrez des erreurs de permissions, vous pouvez rajouter ces lignes suivantes :
#after "deploy:finalize_update" do
#run "chown -R joris:www-data #{latest_release}"
#run "sudo chmod -R 777 #{latest_release}/#{cache_path}"
#run "sudo chmod -R 777 #{latest_release}/#{log_path}"
#end