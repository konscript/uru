  <div id="information">
    <ul>
      <?php foreach ($informations as $information) { ?>
      <li><a href="<?php echo str_replace('&', '&amp;', $information['href']); ?>"><?php echo $information['title']; ?></a></li>
      <?php } ?>
      <li><a href="<?php echo str_replace('&', '&amp;', $contact); ?>"><?php echo $text_contact; ?></a></li>
    </ul>
  </div>
