<style>
.title {
    <?php $title_style->echo_css(0.8)?>
    line-height: normal!important;
    margin: 0;
    text-align: center;
    padding: 10px 0;
}
.text {
    <?php $text_style->echo_css()?>
    padding: 10px 0;
    line-height: 1.5em!important;
    text-align: center;
    margin: 0;
}
.button {
    padding: 10px 0;
}
</style>

<div dir="rtl">

    <table width="<?php echo $td_width ?>" align="right" class="responsive" border="0" cellspacing="0" cellpadding="0">
        <tr>
            <td align="center" valign="top">
                <?php echo $media ? TNP_Composer::image($media) : ''; ?>
            </td>
        </tr>
    </table>

    <table width="<?php echo $td_width ?>" align="left" class="responsive" border="0" cellspacing="0" cellpadding="0">
        <tr>
            <td inline-class="title" dir="ltr">
                <?php echo $event->post_title; ?>
            </td>
        </tr>
        <?php if($event->post_content): ?>
        <tr>
            <td inline-class="text" dir="ltr">
                <?php echo $event->post_content; ?>
            </td>
        </tr>
        <?php endif; ?>
        <tr>
            <td align="center" inline-class="button" dir="ltr">
                <?php echo TNP_Composer::button($button_options) ?>
            </td>
        </tr>
    </table>

</div>
