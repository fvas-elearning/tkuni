<?php
namespace App\Controller\Ui\Course;

use Dom\Template;
use Tk\Form\Field;
use Tk\Request;
use \App\Controller\Iface;

/**
 *
 *
 * @author Michael Mifsud <info@tropotek.com>
 * @link http://www.tropotek.com/
 * @license Copyright 2015 Michael Mifsud
 */
class Manager extends Iface
{

    /**
     * @var \Tk\Table
     */
    protected $table = null;

    /**
     * @var \App\Db\Institution
     */
    private $institution = null;


    /**
     *
     */
    public function __construct()
    {
        parent::__construct('Course Manager');
    }

    /**
     *
     * @param Request $request
     * @return \App\Page\Iface|Template|string
     */
    public function doDefault(Request $request)
    {
        $this->institution = $this->getUser()->getInstitution();
        
        $this->table = new \Tk\Table('CourseList');
        $this->table->setParam('renderer', \Tk\Table\Renderer\Dom\Table::create($this->table));

        $this->table->addCell(new \Tk\Table\Cell\Checkbox('id'));
        $this->table->addCell(new \Tk\Table\Cell\Text('name'))->addCellCss('key')->setUrl(\App\Uri::createHomeUrl('/courseEdit.html'));
        $this->table->addCell(new \Tk\Table\Cell\Text('code'));
        $this->table->addCell(new \Tk\Table\Cell\Text('email'));
        $this->table->addCell(new \Tk\Table\Cell\Date('start'));
        $this->table->addCell(new \Tk\Table\Cell\Date('finish'));
        $this->table->addCell(new \Tk\Table\Cell\Date('created'));

        // Filters
        $this->table->addFilter(new Field\Input('keywords'))->setLabel('')->setAttr('placeholder', 'Keywords');

        // Actions
        //$this->table->addAction(\Tk\Table\Action\Button::getInstance('New Course', 'fa fa-plus', \Tk\Uri::create('/client/courseEdit.html')));
        $this->table->addAction(\Tk\Table\Action\Delete::getInstance());
        $this->table->addAction(\Tk\Table\Action\Csv::getInstance($this->getConfig()->getDb()));

        $filter = $this->table->getFilterValues();
        $filter['institutionId'] = $this->institution->id;
        $users = \App\Db\CourseMap::create()->findFiltered($filter, $this->table->makeDbTool('a.id'));
        $this->table->setList($users);

        return $this->show();
    }

    /**
     * @return \App\Page\Iface
     */
    public function show()
    {
        $template = $this->getTemplate();

        $template->setAttr('new', 'href', \App\Uri::createHomeUrl('/courseEdit.html'));

        $template->replaceTemplate('table', $this->table->getParam('renderer')->show());
        return $this->getPage()->setPageContent($template);
    }


    /**
     * DomTemplate magic method
     *
     * @return Template
     */
    public function __makeTemplate()
    {
        $xhtml = <<<XHTML
<div class="row">

  <div class="col-lg-12">
    <div class="panel panel-default">
      <div class="panel-heading">
        <i class="fa fa-cogs fa-fw"></i> Actions
      </div>
      <div class="panel-body ">
        <div class="row">
          <div class="col-lg-12">
            <a href="javascript: window.history.back();" class="btn btn-default"><i class="fa fa-arrow-left"></i> <span>Back</span></a>
            <a href="/client/courseEdit.html" class="btn btn-default" var="new"><i class="fa fa-graduation-cap"></i> <span>New Course</span></a>
          </div>
        </div>
      </div>
    </div>
  </div>
  
  <div class="col-lg-12">
    <div class="panel panel-default">
      <div class="panel-heading">
        <i class="fa fa-graduation-cap fa-fw"></i> Course
      </div>
      <div class="panel-body">
        <div var="table"></div>
      </div>
    </div>
  </div>
</div>
XHTML;

        return \Dom\Loader::load($xhtml);
    }


}