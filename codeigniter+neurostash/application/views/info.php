<?php include_once(__DIR__.'..\..\controllers\class_factory.php'); ?>

<html>
<head>
	<title>Info - <?php echo ucfirst($data); ?></title>
    <meta charset="utf-8">
    <!-- Bootstrap -->
    <link href="http://bootswatch.com/yeti/bootstrap.css" rel="stylesheet"><!-- Bootstrap -->
    <!-- Fonts Awesome -->
    <link href="//netdna.bootstrapcdn.com/font-awesome/4.0.3/css/font-awesome.css" rel="stylesheet"><!-- Fonts Awesome -->
</head>
<body>
	<div class="container">
		<div class="row">
			<div class="col-xs-12 col-sm-12 col-md-12 col-lg-12">
				<center><h1><strong><?php echo ucfirst($data); ?></strong></h1></center>
			</div>
		</div><!-- row -->
		
		<div class="row">
			<div class="col-xs-12 col-sm-12 col-md-12 col-lg-12">
				<h3>Sample <strong>GET</strong> Request</h3>
			</div>
		</div><!-- row -->

		<div class="row">
			<table class="table">
				<tbody>
					<tr>
						<td>
							Read by <strong>ID</strong>
						</td>
						<td>
							<?php echo "/read/{$data}/1"; ?>
						</td>
					</tr>
					<tr>
						<td>
							Read by <strong>ID</strong> including relations
						</td>
						<td>
							<?php echo "/read/{$data}/1?complete=true"; ?>
						</td>
					</tr>
					<tr>
						<td>
							Changing the default <strong>output format</strong>
						</td>
						<td>
							<?php echo "/read/{$data}/1?format=array"; ?>
						</td>
					</tr>
					<tr>
						<td>
							Read by <strong>own attribute</strong>
						</td>
						<td>
							<?php echo "/read/{$data}?firstname=jennifer"; ?>
						</td>
					</tr>
					<tr>
						<td>
							Read by <strong>related attribute</strong>
						</td>
						<td>
							<?php echo "/read/{$data}?entity_name=something"; ?>
						</td>
					</tr>
					<tr>
						<td>
							Using multiple <strong>attributes</strong>
						</td>
						<td>
							<?php echo "/read/{$data}?firstname=jennifer&genre_name=action"; ?>
						</td>
					</tr>
				</tbody>
			</table>
		</div><!-- row -->

		</br>
		
		<div class="row">
			<div class="col-xs-12 col-sm-12 col-md-12 col-lg-12">
				<h3>System Parameters</h3>
			</div>
		</div><!-- row -->

		</br>

		<div class="row">
			<table class="table">
				<thead>
					<th>Parameter</th>
					<th>Options</th>
				</thead>
				<tbody>
					<tr>
						<td class="col-xs-2 col-sm-2 col-md-2 col-lg-2">
							format
						</td>
						<td class="col-xs-10 col-sm-10 col-md-10 col-lg-10">
							format={object || serialized || array || json || xml}
							</br>
							<strong>default:</strong> json
						</td>
					</tr>
					<tr>
						<td class="col-xs-2 col-sm-2 col-md-2 col-lg-2">
							complete
						</td>
						<td class="col-xs-10 col-sm-10 col-md-10 col-lg-10">
							complete={true || false}
							</br>
							<strong>default:</strong> false
						</td>
					</tr>
					<tr>
						<td class="col-xs-2 col-sm-2 col-md-2 col-lg-2">
							limit
						</td>
						<td class="col-xs-10 col-sm-10 col-md-10 col-lg-10">
							limit={an integer}
							</br>
							<strong>default:</strong> 5
						</td>
					</tr>
					<tr>
						<td class="col-xs-2 col-sm-2 col-md-2 col-lg-2">
							offset
						</td>
						<td class="col-xs-10 col-sm-10 col-md-10 col-lg-10">
							offset={an integer}
							</br>
							<strong>default:</strong> 0
						</td>
					</tr>
				</tbody>
			</table>
		</div>

		</br>

		<div class="row">
			<div class="col-xs-12 col-sm-12 col-md-12 col-lg-12">
				<h3>Read Data Parameters</h3>
			</div>
		</div><!-- row -->

		</br>

		<div class="row">
			<table class="table">
				<thead>
					<th>Field</th>
					<th>Example</th>
				</thead>
				<tbody>
				
				<?php foreach ($data as $attribute => $value): ?>
					<tr>
						<td class="col-xs-2 col-sm-2 col-md-2 col-lg-2">
							<?php echo "{$attribute}<br>";?>
						</td>
						<td class="col-xs-10 col-sm-10 col-md-10 col-lg-10">
							<?php echo "/read/{$data}?{$attribute}={your input}"; ?>
						</td>
					</tr>
				<?php endforeach ?>
				
				<?php if ( ! empty($data->relationship())): ?>
					<?php foreach ($data->relationship() as $ent => $val): ?>
						<?php
							$class_factory = new Class_Factory($ent);
							$obj = $class_factory->get_concrete_class();
						?>
						<?php foreach ($obj as $attr => $val): ?>
							<tr>
								<td class="col-xs-2 col-sm-2 col-md-2 col-lg-2">
									<?php echo "{$obj}.{$attr}<br>";?>
								</td>
								<td class="col-xs-10 col-sm-10 col-md-10 col-lg-10">
									<?php echo "/read/{$data}?{$obj}_{$attr}={your input}"; ?>
								</td>
							</tr>
						<?php endforeach ?>
					<?php endforeach ?>
				<?php endif; ?>

				</tbody>
			</table>
		</div><!-- row -->

	</div><!-- container -->
</body>
</html>