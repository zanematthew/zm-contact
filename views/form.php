<?php

$post_type = 'zmcontact';
$post_type_obj = get_post_types( array( 'name' => $post_type), 'objects' );

$submission = New zMSubmission;

?>


<div <?php post_class('freelance-contact-container zm-default-form-container'); ?>>
    <form action="#" method="POST" id="contact_form">

        <!-- Print hidden failure, success, etc. message -->
        <?php $submission->status(); ?>

        <!-- Print hidden nounce, secutiry, ajax refer variables -->
        <?php $submission->security_fields( 'postTypeSubmit', $post_type ); ?>

        <div class="row-container">

            <!-- Category -->
            <div class="row">
                <?php zMHelpers::build_options( array(
                    'extra_data' => 'style="width: 200px;" data-placeholder="Please choose a category"',
                    'extra_class' => 'zm_validate_required chzn-select',
                    'taxonomy' => 'zmcontact_category',
                    'label' => 'I have a message for...'
                    ) ); ?>
            </div>
            <!-- -->

            <!-- Budget -->
            <div class="row">
                <?php //zMHelpers::build_options( array( 'extra_data' => 'style="width: 200px;" data-placeholder="Please choose a budget"', 'extra_class' => 'chzn-select', 'taxonomy' => 'contact_budget', 'label' => 'Budget' ) ); ?>
            </div>
            <!-- -->

            <!-- Subject -->
            <div class="row">
                <label>Subject<sup class="req">&#42;</sup></label>
                <input type="text" name="post_title" id="post_title" class="zm_validate_required" size="65" />
            </div>
            <!-- -->

            <!-- Name -->
            <div class="row">
                <fieldset class="first-name">
                    <label>First Name</label>
                    <input type="text" name="<?php print $post_type; ?>_first-name" />
                </fieldset>
                <fieldset class="last-name">
                    <label>Last Name</label>
                    <input type="text" name="<?php print $post_type; ?>_last-name" />
                </fieldset>
            </div>
            <!-- -->


            <!-- Contact -->
            <div class="row">
                <fieldset class="phone-number">
                    <label>Phone Number</label>
                    <input type="text" name="<?php print $post_type; ?>_phone-number" />
                </fieldset>
                <fieldset class="email">
                    <label>Email</label>
                    <input type="text" class="" name="<?php print $post_type; ?>_email" />
                </fieldset>
            </div>
            <!-- -->



            <!-- Website -->
            <div class="row">
                <fieldset class="">
                    <label>Website</label>
                    <input type="text" size="65" class="" name="<?php print $post_type; ?>_website" />
                </fieldset>
            </div>
            <!-- -->

            <!-- Message -->
            <div class="row">
                <label>Message</label><textarea name="content" rows="6"></textarea>
            </div>
            <!--  -->
        </div>

        <div class="button-container">
            <div class="left">
                <input id="" class="save button" disabled type="submit" value="Submit" name="save_exit" data-post_type="<?php print $post_type; ?>"/>
            </div>
        </div>
    </form>
</div>