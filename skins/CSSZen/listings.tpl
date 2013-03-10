<!-- START BLOCK : listings -->
{seriesheader}
<!-- START BLOCK : seriesblock -->
<div class="listbox">
	<div class="title">{title} by {author} {score} [{reviews} - {numreviews}] </div>
	<div class="content"><span class="label">Summary: </span>{summary}<br />
		<span class="label">Categories:</span> {category} <br />
		<span class="label">Characters: </span>{characters}<br />
		{classifications}
		<span class="label">Open:</span> {open}
		{adminoptions}
	</div>
	<div class="tail">{reportthis}&nbsp;</div>
</div>
{comment}
<!-- END BLOCK : seriesblock -->
{stories}
<!-- START BLOCK : storyblock -->
<div class="listbox">
	<div class="title">{title} by {author} <span class="label">Rated:</span> {rating} {roundrobin} {score} [{reviews} - {numreviews}] {new} </div>
	<div class="content"><span class="label">Summary: </span>{featuredstory}{summary}<br />
		<span class="label">Categories:</span> {category} <span class="label">Characters: </span> {characters}<br />
		{classifications}
		<span class="label">Series:</span> {serieslinks}<br />
		<span class="label">Chapters: </span> {numchapters} {toc}<br />
		<span class="label">Completed:</span> {completed}  
		<span class="label">Word count:</span> {wordcount} <span class="label">Read Count:</span> {count}
		{adminlinks}</div>
	<div class="tail"><span class="label">{addtofaves} Published: </span>{published} <span class="label">Updated:</span> {updated} {reportthis}</div>
</div>
{comment}
<!-- END BLOCK : storyblock -->
{pagelinks}
<!-- END BLOCK : listings -->