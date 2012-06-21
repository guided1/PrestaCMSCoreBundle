<?php
/**
 * This file is part of the Presta Bundle project.
 *
 * @author Nicolas Bastien nbastien@prestaconcept.net
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */
namespace PrestaCMS\CoreBundle\Controller\Admin;

use PrestaSonata\AdminBundle\Controller\Admin\Controller as AdminController;
use PrestaCMS\CoreBundle\Form\PageType;

use Application\PrestaCMS\CoreBundle\Entity\Page;

/**
 * Page administration controller
 * 
 * @package    PrestaCMS
 * @subpackage CoreBundle
 * @author     Nicolas Bastien nbastien@prestaconcept.net
 */
class PageController extends AdminController
{
    /**
     * Return Website manager
     * 
     * @return PrestaCMS\CoreBundle\Model\WebsiteManager 
     */
    public function getWebsiteManager()
    {
        return $this->get('presta_cms.website_manager');
    }
    
    /**
     * Return Theme manager
     * 
     * @return PrestaCMS\CoreBundle\Model\ThemeManager 
     */
    public function getThemeManager()
    {
        return $this->get('presta_cms.theme_manager');
    }
    
    /**
     * Return Page manager
     * 
     * @return PrestaCMS\CoreBundle\Model\PageManager 
     */
    public function getPageManager()
    {
        return $this->get('presta_cms.page_manager');
    }
    
    /**
     * Page administration main screen 
     */
    public function indexAction($id, $website_id, $locale)
    {
        $viewParams = array(
            'id' => $id, 'website_id' => $website_id, 'locale' => $locale, 'navigations' => array(), 'page' => null
        );
        
        $website = $this->getWebsiteManager()->getWebsite($website_id, $locale);        
        if ($website != null) {
            $theme = $this->getThemeManager()->getTheme($website->getTheme());

            $navigations = array();
            foreach ($theme->getNavigations() as $navigation) {
                $navigations[$navigation] = $this->getPageManager()->getNavigationTree($website, $navigation);
            }
            $navigations['single_pages'] = $this->getPageManager()->getSinglePagesTree($website);
            $viewParams['theme'] = $theme;
            $viewParams['navigations'] = $navigations;
            $viewParams['page'] = $this->getPageManager()->getPageById($website, $id);            
        }
        
        if ($id) {
            $page = $this->getPageManager()->getPageById($website, $id);
            
            $form = $this->createForm(new PageType(), $page);
            if ($this->get('request')->getMethod() == 'POST') {
                $form->bindRequest($this->get('request'));
                
                if ($form->isValid()) {
                    $this->getPageManager()->update($page);
                    $this->get('session')->setFlash('sonata_flash_success', 'flash_edit_success');
                } else {
                    $this->get('session')->setFlash('sonata_flash_error', 'flash_edit_error');
                }
            }            
            
            $viewParams['page'] = $page; 
            $viewParams['form'] = $form->createView();
        }
        
        return $this->render('PrestaCMSCoreBundle:Admin/Page:index.html.twig', $viewParams);
    }    
}