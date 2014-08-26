<?php
	$title = 'Partners';
	$links = ['Program Policies' => 'policies'];
	$partners = getPartners();
?>

<div class="left">
	<h2>What to Share with Students</h2>
	<p>
		Share the following points with your students at your events!
	</p>
	<ul>
		<li>
			<p>
				LINK is a chance for you to find out about great educational and
				involvement based events going on around campus, and anyone is welcome to attend the events.
			</p>
		</li>
		<li>
			<p>
				If you are a Freshman, then you can earn LINK Loot at events and use your accumulated
				Loot at various events throughout the year. There is a Summer B Lottery, Fall Tuition
				Scholarship and Spring Auction. The Lottery and Auction come with a chance to bid on
				amazing prizes from TVs, Kindles, gift cards and more, and anyone with 10,000 Loot or more
				will be entered into a raffle to win a $1,500 SGA Tuition Scholarship in the Fall.
			</p>
		</li>
		<li>
			<p>
				After your Freshman year, continue to check out the LINK website and attend as
				many events as you wish!
			</p>
		</li>
	</ul>
</div>
<div class="sidebar-right">
	<img src="images/logo.jpg" alt="" class="floatright" />

	<div class="hr-clear"></div>

	<ul class="link-list bullets">
		<li><a href="docs/program_signin_sheet.pdf">Program Sign-In Sheet</a></li>
	</ul>
</div>
<div class="hr-clear"></div>

<h2>Current Partners</h2>
<p>
	Below is a list of departments, units, organizations, and other partners that bring
	events to the LINK program. If you are interested in becoming a
	partner with the LINK Program, please <a href="contact">contact us</a>.
</p>

<table class="grid smaller partners">
	<tr>
		<th scope="col">Partner</th>
		<th scope="col">Phone</th>
		<th scope="col">Email</th>
		<th scope="col">Campus Location</th>
	</tr>
	<?php foreach($partners as $row): ?>
	<tr>
		<td><a href="<?= $row['departmentUrl'] ?>"><?= $row['departmentName'] ?></a></td>
		<td><?= $row['departmentPhone'] ?></td>
		<td><a href="mailto:<?= $row['departmentEmail'] ?>"><?= $row['departmentEmail'] ?></a></td>
		<td><a href="http://map.ucf.edu/?show=<?= $row['departmentMapId'] ?>"><?= $row['departmentLocation'] ?></a></td>
	</tr>
	<?php endforeach ?>
</table>