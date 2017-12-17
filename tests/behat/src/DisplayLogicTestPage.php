<?php

namespace UncleCheese\DisplayLogic\Tests\Behaviour;

use Page;
use SilverStripe\Forms\TextField;
use SilverStripe\Forms\CheckboxField;
use SilverStripe\Forms\CheckboxSetField;
use UncleCheese\DisplayLogic\Forms\Wrapper;
use SilverStripe\AssetAdmin\Forms\UploadField;
use SilverStripe\Forms\LiteralField;
use SilverStripe\Forms\OptionsetField;
use SilverStripe\Forms\DropdownField;
use SilverStripe\Forms\ReadonlyField;
use SilverStripe\Forms\TextareaField;
use SilverStripe\CMS\Model\SiteTree;
use SilverStripe\Dev\TestOnly;

class DisplayLogicTestPage extends Page implements TestOnly
{
    public function getCMSFields() {
        $f = parent::getCMSFields();
        $f->addFieldsToTab("Root.Test", array(
            TextField::create("Name","Name of event"),
            TextField::create("VenueSize","Size of venue"),
            CheckboxField::create("Refreshments","This is a large venue. Are there refreshments?")
                ->displayIf("VenueSize")->isGreaterThan(100)->end(),
            CheckboxSetField::create("Vendors","Vendors", Member::get()->map())
                ->displayIf("Refreshments")->isChecked()->end(),
            TextField::create("TentSize","You're going to need a tent. What size is it?")
                ->displayIf("Vendors")->hasCheckedAtLeast(3)->end(),
            CheckboxField::create('HasUpload', 'Has an upload'),

            Wrapper::create(
                UploadField::create('FileUpload', 'Upload a file'),
                LiteralField::create('test', '<strong>Keep the file small!</strong>')
            )->displayIf('HasUpload')->isChecked()->end(),

            OptionSetField::create("LinkType", "", array('internal' => 'Link to an internal page', 'external' => 'Link to an external page')),
            DropdownField::create("InternalLinkID", "Choose a page", SiteTree::get()->map()->toArray())->setEmptyString("-- choose --")
                ->displayIf("LinkType")->isEqualTo("internal")
                ->end(),
            TextField::create("ExternalLink", "Link to external page")
                ->displayIf("LinkType")->isEqualTo("external")
                ->end(),
            Wrapper::create(
                ReadonlyField::create('URL','Base URL','http://example.com')
            )->displayIf('LinkType')->isEqualTo('internal')->end(),
            TextField::create("LinkLabel", "Label for link")
                ->displayIf("LinkType")->isChecked()->end(),
            CheckboxField::create("UseEmbedCode","I have embed code")
                ->displayIf("LinkType")->isEqualTo("external")
                ->end(),
            TextareaField::create("EmbedCode","Enter the embed code.")
                ->displayIf("UseEmbedCode")->isChecked()
                ->orIf()
                ->group()
                ->orIf("ExternalLink")->contains("youtube.com")
                ->orIf("ExternalLink")->contains("vimeo.com")
                ->end()
        ));

        return $f;
    }
}