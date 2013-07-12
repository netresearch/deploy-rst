<?php
namespace netresearch\DeployRst;
require_once 'netresearch/DeployRst/Driver/Confluence/Filter.php';
require_once 'netresearch/DeployRst/Driver/Confluence/Filter/Aida.php';

class Driver_Confluence_Filter_AidaTest extends \PHPUnit_Framework_TestCase
{
    public function setUp()
    {
        $this->filter = new Driver_Confluence_Filter_Aida();
    }

    public function testPreFilterRemoveHeadings()
    {
        $this->assertEquals(
            <<<RST

Foo bar
RST
            ,
            $this->filter->preFilter(
                <<<RST
*************
Extensionname
*************

Foo bar
RST
            )
        );
    }

    public function testPreFilterRemoveHeadingsNoHeading()
    {
        $this->assertEquals(
            <<<RST
Hi there.
*************
Extensionname
*************

RST
            ,
            $this->filter->preFilter(
                <<<RST
Hi there.
*************
Extensionname
*************

RST
            )
        );
    }

    public function testAddNumberedHeadings()
    {
        $this->filter->doc = <<<CFL
Foo

CFL;
        $this->filter->addNumberedHeadings();
        $this->assertEquals(
            <<<CFL
{numberedheadings}
Foo
{numberedheadings}

CFL
            ,
            $this->filter->doc
        );
    }

    public function testFixImages()
    {
        $this->filter->doc = <<<CFL
!doc/fe-plugin-support.png|align=right,thumbnail,title=Rückrufformular!
!doc/foo/fe-plugin-support.png|align=right,thumbnail,title=foo!
CFL;
        $this->filter->fixImages();
        $this->assertEquals(
            <<<CFL
!fe-plugin-support.png|align=right,thumbnail,title=Rückrufformular!
!foo-fe-plugin-support.png|align=right,thumbnail,title=foo!
CFL
            ,
            $this->filter->doc
        );
    }

    public function testFixImagesGallery()
    {
        $this->filter->doc = <<<CFL
{gallery:include=doc/fe.png,doc/b.jpg,doc/foo/c.png}
{gallery:include=doc/gsa-treffer-0.jpg,doc/gsa-treffer-1.jpg,doc/gsa-treffer-2.jpg,doc/gsa-treffer-3.jpg}
CFL;
        $this->filter->fixImages();
        $this->assertEquals(
            <<<CFL
{gallery:include=fe.png,b.jpg,foo-c.png}
{gallery:include=gsa-treffer-0.jpg,gsa-treffer-1.jpg,gsa-treffer-2.jpg,gsa-treffer-3.jpg}
CFL
            ,
            $this->filter->doc
        );
    }


    public function testFixToc()
    {
        $this->filter->doc = <<<CFL
Foo

{toc}

Bar
CFL;
        $this->filter->fixToc();
        $this->assertEquals(
            <<<CFL
Foo

{panel}
{toc:maxLevel=4}
{panel}

Bar
CFL
            ,
            $this->filter->doc
        );
    }

    public function testShowAttachments()
    {
        $this->filter->doc = <<<CFL
Foo

CFL;
        $this->filter->showAttachments();
        $this->assertEquals(
            <<<CFL
Foo

h1. Attachments
{attachments}

CFL
            ,
            $this->filter->doc
        );
    }

}



?>
