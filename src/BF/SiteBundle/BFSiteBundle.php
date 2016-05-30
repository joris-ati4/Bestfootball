<?php

namespace BF\SiteBundle;

use Symfony\Component\HttpKernel\Bundle\Bundle;
use Symfony\Component\Routing\Generator\UrlGeneratorInterface;

use Presta\SitemapBundle\Event\SitemapPopulateEvent;
use Presta\SitemapBundle\Sitemap\Url\UrlConcrete;

class BFSiteBundle extends Bundle
{

	public function boot()
    {
        $router = $this->container->get('router');
        $event  = $this->container->get('event_dispatcher');
        

        //listen presta_sitemap.populate event
        $event->addListener(
            SitemapPopulateEvent::ON_SITEMAP_POPULATE,
            function(SitemapPopulateEvent $event) use ($router){
                //get absolute homepage url
                $url = $router->generate('bf_site_homepage', array(), UrlGeneratorInterface::ABSOLUTE_URL);

                //add homepage url to the urlset named default
                $event->getGenerator()->addUrl(
                    new UrlConcrete(
                        $url,
                        new \DateTime(),
                        UrlConcrete::CHANGEFREQ_HOURLY,
                        1
                    ),
                    'default'
                );
        });
        $event->addListener(
            SitemapPopulateEvent::ON_SITEMAP_POPULATE,
            function(SitemapPopulateEvent $event) use ($router){
                //get absolute homepage url
                $url = $router->generate('bf_site_challenges', array(), UrlGeneratorInterface::ABSOLUTE_URL);

                //add homepage url to the urlset named default
                $event->getGenerator()->addUrl(
                    new UrlConcrete(
                        $url,
                        new \DateTime(),
                        UrlConcrete::CHANGEFREQ_MONTHLY,
                        1
                    ),
                    'default'
                );
        });
        $event->addListener(
            SitemapPopulateEvent::ON_SITEMAP_POPULATE,
            function(SitemapPopulateEvent $event) use ($router){
                //get absolute homepage url
                $url = $router->generate('bf_site_partnerChallenges', array(), UrlGeneratorInterface::ABSOLUTE_URL);

                //add homepage url to the urlset named default
                $event->getGenerator()->addUrl(
                    new UrlConcrete(
                        $url,
                        new \DateTime(),
                        UrlConcrete::CHANGEFREQ_MONTHLY,
                        1
                    ),
                    'default'
                );
        });
        $event->addListener(
            SitemapPopulateEvent::ON_SITEMAP_POPULATE,
            function(SitemapPopulateEvent $event) use ($router){
                //get absolute homepage url
                $url = $router->generate('bf_site_about', array(), UrlGeneratorInterface::ABSOLUTE_URL);

                //add homepage url to the urlset named default
                $event->getGenerator()->addUrl(
                    new UrlConcrete(
                        $url,
                        new \DateTime(),
                        UrlConcrete::CHANGEFREQ_MONTHLY,
                        0.5
                    ),
                    'default'
                );
        });
        $event->addListener(
            SitemapPopulateEvent::ON_SITEMAP_POPULATE,
            function(SitemapPopulateEvent $event) use ($router){
                //get absolute homepage url
                $url = $router->generate('bf_site_rules', array(), UrlGeneratorInterface::ABSOLUTE_URL);

                //add homepage url to the urlset named default
                $event->getGenerator()->addUrl(
                    new UrlConcrete(
                        $url,
                        new \DateTime(),
                        UrlConcrete::CHANGEFREQ_MONTHLY,
                        1
                    ),
                    'default'
                );
        });
        $event->addListener(
            SitemapPopulateEvent::ON_SITEMAP_POPULATE,
            function(SitemapPopulateEvent $event) use ($router){
                //get absolute homepage url
                $url = $router->generate('bf_site_contact', array(), UrlGeneratorInterface::ABSOLUTE_URL);

                //add homepage url to the urlset named default
                $event->getGenerator()->addUrl(
                    new UrlConcrete(
                        $url,
                        new \DateTime(),
                        UrlConcrete::CHANGEFREQ_MONTHLY,
                        1
                    ),
                    'default'
                );
        });
        $event->addListener(
            SitemapPopulateEvent::ON_SITEMAP_POPULATE,
            function(SitemapPopulateEvent $event) use ($router){
                //get absolute homepage url
                $url = $router->generate('bf_site_partners', array(), UrlGeneratorInterface::ABSOLUTE_URL);

                //add homepage url to the urlset named default
                $event->getGenerator()->addUrl(
                    new UrlConcrete(
                        $url,
                        new \DateTime(),
                        UrlConcrete::CHANGEFREQ_MONTHLY,
                        1
                    ),
                    'default'
                );
        });
        $event->addListener(
            SitemapPopulateEvent::ON_SITEMAP_POPULATE,
            function(SitemapPopulateEvent $event) use ($router){
                //get absolute homepage url
                $url = $router->generate('bf_site_press', array(), UrlGeneratorInterface::ABSOLUTE_URL);

                //add homepage url to the urlset named default
                $event->getGenerator()->addUrl(
                    new UrlConcrete(
                        $url,
                        new \DateTime(),
                        UrlConcrete::CHANGEFREQ_MONTHLY,
                        1
                    ),
                    'default'
                );
        });
        $event->addListener(
            SitemapPopulateEvent::ON_SITEMAP_POPULATE,
            function(SitemapPopulateEvent $event) use ($router){
                //get absolute homepage url
                $url = $router->generate('bf_site_privacy', array(), UrlGeneratorInterface::ABSOLUTE_URL);

                //add homepage url to the urlset named default
                $event->getGenerator()->addUrl(
                    new UrlConcrete(
                        $url,
                        new \DateTime(),
                        UrlConcrete::CHANGEFREQ_MONTHLY,
                        1
                    ),
                    'default'
                );
        });
        $event->addListener(
            SitemapPopulateEvent::ON_SITEMAP_POPULATE,
            function(SitemapPopulateEvent $event) use ($router){
                //get absolute homepage url
                $url = $router->generate('bf_site_terms', array(), UrlGeneratorInterface::ABSOLUTE_URL);

                //add homepage url to the urlset named default
                $event->getGenerator()->addUrl(
                    new UrlConcrete(
                        $url,
                        new \DateTime(),
                        UrlConcrete::CHANGEFREQ_MONTHLY,
                        1
                    ),
                    'default'
                );
        });
        $event->addListener(
            SitemapPopulateEvent::ON_SITEMAP_POPULATE,
            function(SitemapPopulateEvent $event) use ($router){
                //get absolute homepage url
                $url = $router->generate('bf_site_prices', array(), UrlGeneratorInterface::ABSOLUTE_URL);

                //add homepage url to the urlset named default
                $event->getGenerator()->addUrl(
                    new UrlConcrete(
                        $url,
                        new \DateTime(),
                        UrlConcrete::CHANGEFREQ_MONTHLY,
                        1
                    ),
                    'default'
                );
        });
        $event->addListener(
            SitemapPopulateEvent::ON_SITEMAP_POPULATE,
            function(SitemapPopulateEvent $event) use ($router){
                //get absolute homepage url
                $url = $router->generate('bf_site_freestyle', array(), UrlGeneratorInterface::ABSOLUTE_URL);

                //add homepage url to the urlset named default
                $event->getGenerator()->addUrl(
                    new UrlConcrete(
                        $url,
                        new \DateTime(),
                        UrlConcrete::CHANGEFREQ_MONTHLY,
                        1
                    ),
                    'default'
                );
        });

        //site map for all the challenge views.
        $em = $this->container->get('doctrine')->getManager();
        $listChallenges = $em->getRepository('BFSiteBundle:Challenge')->findBy(array(),array('date' => 'desc'));
        foreach ($listChallenges as $challenge){
            $slug = $challenge->getSlug();
            $event->addListener(
                SitemapPopulateEvent::ON_SITEMAP_POPULATE,
                function(SitemapPopulateEvent $event) use ($router, $slug){
                    //get absolute homepage url
                        $url = $router->generate('bf_site_challengeview', array('slug' => $slug), UrlGeneratorInterface::ABSOLUTE_URL);
                    //add homepage url to the urlset named default
                    $event->getGenerator()->addUrl(
                        new UrlConcrete(
                            $url,
                            new \DateTime(),
                            UrlConcrete::CHANGEFREQ_HOURLY,
                            1
                        ),
                        'challenges'
                    );  
                }
            );
        }

        //site map for all the videos.
        $em = $this->container->get('doctrine')->getManager();
        $listVideos = $em->getRepository('BFSiteBundle:Video')->findBy(array(),array('date' => 'desc'));
        foreach ($listVideos as $video){
            $code = $video->getCode();
            $event->addListener(
                SitemapPopulateEvent::ON_SITEMAP_POPULATE,
                function(SitemapPopulateEvent $event) use ($router, $code){
                    //get absolute homepage url
                        $url = $router->generate('bf_site_video', array('code' => $code), UrlGeneratorInterface::ABSOLUTE_URL);
                    //add homepage url to the urlset named default
                    $event->getGenerator()->addUrl(
                        new UrlConcrete(
                            $url,
                            new \DateTime(),
                            UrlConcrete::CHANGEFREQ_HOURLY,
                            1
                        ),
                        'videos'
                    );  
                }
            );
        }

        //site map for all the challenge views.
        $em = $this->container->get('doctrine')->getManager();
        $listUsers = $em->getRepository('BFUserBundle:User')->findBy(array(),array('id' => 'desc'));
        foreach ($listUsers as $user){
            $username = $user->getUsername();
            $event->addListener(
                SitemapPopulateEvent::ON_SITEMAP_POPULATE,
                function(SitemapPopulateEvent $event) use ($router, $username){
                    //get absolute homepage url
                        $url = $router->generate('bf_site_profile', array('username' => $username), UrlGeneratorInterface::ABSOLUTE_URL);
                    //add homepage url to the urlset named default
                    $event->getGenerator()->addUrl(
                        new UrlConcrete(
                            $url,
                            new \DateTime(),
                            UrlConcrete::CHANGEFREQ_HOURLY,
                            1
                        ),
                        'users'
                    );  
                }
            );
        }
        
   }
}
