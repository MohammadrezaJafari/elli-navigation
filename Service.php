<?php

/**
 * Created by PhpStorm.
 * User: pooria
 * Date: 9/28/15
 * Time: 7:18 PM
 */
namespace Ellie\Service\Navigation;
use Zend\ServiceManager\ServiceLocatorInterface;
use Zend\Navigation\Service\DefaultNavigationFactory;

class Service extends DefaultNavigationFactory
{
    protected function getPages(ServiceLocatorInterface $serviceLocator)
    {

        if (null === $this->pages) {
            //FETCH data from table menu :
            $configuration['navigation'][$this->getName()] = array();
            $application = $serviceLocator->get('Application');
            $language_code = $application->getMvcEvent()->getRouteMatch()->getParam('lang', 'fa');
            //@toDo : use language for translate part

            $result = $serviceLocator->get('Config')['navigation_manager'];



            //$result = array_merge($result, $this->makeNavArray(Null,$serviceLocator),$end);


            $configuration['navigation'][$this->getName()] = $result;


            if (!isset($configuration['navigation'])) {
                throw new Exception\InvalidArgumentException('Could not find navigation configuration key');
            }
            if (!isset($configuration['navigation'][$this->getName()])) {
                throw new Exception\InvalidArgumentException(sprintf(
                    'Failed to find a navigation container by the name "%s"',
                    $this->getName()
                ));
            }


            $routeMatch  = $application->getMvcEvent()->getRouteMatch();
            $router      = $application->getMvcEvent()->getRouter();
            $pages       = $this->getPagesFromConfig($configuration['navigation'][$this->getName()]);
            foreach ($pages as $routeName => $options) {
                foreach ($options['pages'] as $key => $page) {
                    $pages[$routeName]['pages'][$key]['params']['lang'] = $language_code;
                }

            }

            $this->pages = $this->injectComponents($pages, $routeMatch, $router);
        }
        return $this->pages;
    }

    protected function toArray($object,$type){
        $result = array();
        foreach ($object as $row){
            $row =  (array) $row;
            $row["type"] = $type;
            array_push($result, $row);
        }
        return $result;
    }

    protected function mergeByOrder($array1,$array2){
        $result = array();
        while(sizeof($array1)>0 || sizeof($array2)>0)
        {
            //echo sizeof($array1) ."____". sizeof($array2)."<br>";
            $array1Top = array_pop($array1);
            $array2Top = array_pop($array2);

            if($array1Top != null && $array2Top != null)
            {
                if($array1Top["order"] <= $array2Top["order"])
                {
                    array_push($result, $array1Top);
                    array_push($array2, $array2Top);
                }else
                {
                    array_push($array1, $array1Top);
                    array_push($result, $array2Top);
                }
            }else if($array1Top != null)
            {
                array_push($result, $array1Top);
            }else
            {
                array_push($result, $array2Top);
            }


        }

        return $result;
    }

    public function makeNavArray( $parent , ServiceLocatorInterface $serviceLocator){
        $application = $serviceLocator->get('Application');
        $language_code = $application->getMvcEvent()->getRouteMatch()->getParam('language', 'fa');
        $language = $serviceLocator->get('Panel\Model\LanguageTable')->getByCode($language_code);
        $cats = $serviceLocator->get('Panel\Model\CatViewTable')->getByLanguageAndParent((isset($language->id))?$language->id:1,$parent);
        $cats = $this->toArray($cats,"cat");

        $posts= $serviceLocator->get('Panel\Model\PostViewTable')->getByLanguageAndParent((isset($language->id))?$language->id:1,$parent);
        $posts = $this->toArray($posts,"post");
        $fetchMenu = $this->mergeByOrder(array_reverse($cats),array_reverse($posts));
        $temp = array();
        foreach ($fetchMenu as $row)
        {
            // die(var_dump($row));
            if($row["enable"]){
                $name = ($row["type"] == "post")?"subject":"name";
                $temp[$row['idname']] = array(
                    'label' => $row[$name],
                    'route' => 'website_cats_&_posts',
                    'controller' => $row['type'],
                    'inmenu'=>$row["inmenu"],
                    'params' => array(
                        'id'=>$row["id"],
                        'language'=> $row["language_code"],
                        'action' => "index",
                        "name"=> $row[$name]
                    ),
                    'pages' => ($row['type'] == "cat")?$this->makeNavArray($row['id'], $serviceLocator):array()
                );
            }
        }

        return $temp;
    }

}