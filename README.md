# Display Logic Module for SilverStripe 4

## Description
The Display Logic module allows you to add conditions for displaying or hiding certain form fields based on client-side behavior.

## Example usage
```php
<?php
$products->displayIf("HasProducts")->isChecked();

$sizes->hideUnless("ProductType")->isEqualTo("t-shirt")
      ->andIf("Price")->isGreaterThan(10);
      
$payment->hideIf("Price")->isEqualTo(0);

$shipping->displayIf("ProductType")->isEqualTo("furniture")
           ->andIf()
              ->group()
                ->orIf("RushShipping")->isChecked()
                ->orIf("ShippingAddress")->isNotEmpty()
              ->end();
```

## Available comparison functions
    - isEqualTo
    - isNotEqualTo
    - isGreaterThan
    - isLessThan
    - contains
    - startsWith
    - endsWith
    - isEmpty
    - isNotEmpty
    - isBetween
    - isChecked
    - isNotChecked()
    - hasCheckedOption
    - hasCheckedAtLeast
    - hasCheckedLessThan
    
## Available display conditions
    - displayIf()
    - displayUnless()
    - hideIf()
    - hideUnless()

## Kitchen sink example
```php
<?php
  public function getCMSFields() {
		$fields = parent::getCMSFields();
		$fields->addFieldsToTab("Root.Test", array(
			TextField::create("Name","Name of event"),
			TextField::create("VenueSize","Size of venue"),
			$refreshments = CheckboxField::create("Refreshments","This is a large venue. Are there refreshments?"),							
			$vendors = CheckboxSetField::create("Vendors","Vendors", Member::get()->map()),				
			$tent = TextField::create("TentSize","You're going to need a tent. What size is it?"),

			OptionSetField::create("LinkType", "", array('internal' => 'Link to an internal page', 'external' => 'Link to an external page')),
			$internal = DropdownField::create("InternalLinkID", "Choose a page", SiteTree::get()->map()->toArray())->setEmptyString("-- choose --"),
			$external = TextField::create("ExternalLink", "Link to external page"),
			$label = TextField::create("LinkLabel", "Label for link"),

			$useEmbed = CheckboxField::create("UseEmbedCode","I have embed code"),
			$embed = TextareaField::create("EmbedCode","Enter the embed code.")
			
		));						
		
		$refreshments->displayIf("VenueSize")->isGreaterThan(100);
		$vendors->displayIf("Refreshments")->isChecked();
		$tent->displayIf("Vendors")->hasCheckedAtLeast(3);


		$internal->displayIf("LinkType")->isEqualTo("internal");				
		$external->displayIf("LinkType")->isEqualTo("external");
		$label->displayIf("LinkType")->isEqualTo("internal")->orIf("LinkType")->isEqualTo("external");
		
		$useEmbed->displayIf("LinkType")->isEqualTo("external");
		$embed->displayIf("UseEmbedCode")->isChecked()
					->orIf()
						->group()
							->orIf("ExternalLink")->contains("youtube.com")
							->orIf("ExternalLink")->contains("vimeo.com")
						->end();


		return $fields;
	}
```

## Kitchen sink example, with chaining
```php
<?php

use UncleCheese\DisplayLogic\Wrapper;
//...
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
            
            DisplayLogicWrapper::create(
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
```

## Dealing with non-standard form fields
Sometimes you will want to wrap display logic around a form field that does not use the standard FormField template, such as GridField or LiteralField. For these cases, you can wrap the form field in UncleCheese\DisplayLogic\Wrapper.
```php
$fields->addFieldToTab("Root.Main", Wrapper::create(
		LiteralField::create("foo","<h2>Hello</h2>")
	)
	->displayIf("Title")->isEmpty()->end()
);
```

## What's the difference between displayIf() and hideUnless()?
Not much. hideUnless() will take a CSS class that hides it by default before the script loads, to eliminate any flash of form fields before the script has loaded. In general, use displayIf() when the common case is for the field to be hidden, and hideUnless() when the common case is for the field to be shown.
The same logic applies to hideIf() and displayUnless().


              
