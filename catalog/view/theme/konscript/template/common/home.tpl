<?php echo $header; ?><?php echo $column_left; ?><?php echo $column_right; ?>
<div id="content">
  
  <?php /*foreach ($modules as $module) { ?>
  <?php echo ${$module['code']}; ?>
  <?php }*/ ?>

  <div class="top">
    <div class="left"></div>
    <div class="right"></div>
    <div class="center">
      <h1>URU Master Pieces</h1>
    </div>
  </div>

<div class="middle">  
    <div class="center">
<?php /* START: Added for homepage category hacks */ ?>
      <?php if ($categories) { ?>
        <table class="list">
          <?php for ($i = 0; $i < sizeof($categories); $i = $i + 4) { ?>
          <tr>
            <?php for ($j = $i; $j < ($i + 4); $j++) { ?>
            <td width="25%"><?php if (isset($categories[$j])) { ?>
              <a href="<?php echo $categories[$j]['href']; ?>"><img src="<?php echo $categories[$j]['thumb']; ?>" title="<?php echo $categories[$j]['name']; ?>" alt="<?php echo $categories[$j]['name']; ?>" style="margin-bottom: 3px;" /></a><br />
              <a href="<?php echo $categories[$j]['href']; ?>"><?php echo $categories[$j]['name']; ?></a>
              <?php } ?></td>
            <?php } ?>
          </tr>
          <?php } ?>
        </table>
        <?php } ?>
<?php /* END: Added for homepage category hacks */ ?>      
	</div>
</div>

  <div class="bottom">
    <div class="left"></div>
    <div class="right"></div>
    <div class="center"></div>
  </div>  
</div>
<?php echo $footer; ?> 
