<?php
	error_reporting(E_ALL);
	ini_set('error_reporting', E_ALL);

	$f = file_get_contents( 'registration.json' );
	$timeslots = json_decode( $f, true );

	if ( $_SERVER[ 'REQUEST_METHOD' ] === 'POST' ) {
		$error = null;
		$timeslot = $_POST[ 'timeslot' ];
		if ( ! isset( $timeslots[ $timeslot ] ) ) {
			$error = 'Invalid timeslot.';
		} else {
			$students = array();
			for( $i = 0; $i < 3; $i++ ) {
				$v = intval( $_POST[ 'student-' . $i ] );
				if ( $_POST[ 'student-' . $i ] != '' && $v === 0 ) {
					$error = 'Invalid student ID';
				} else if ( $v !== 0 ) {
					$students[] = $v;
				}
			}

			if ( count( $students ) === 0 ) {
				$error = 'No student IDs are specified';
			}
		}

		if ( $error === null ) {
			// Everything is correct
			$slot = $timeslots[ $timeslot ];
			if ( count( $slot ) < 3 ) {
				$timeslots[ $timeslot ][] = $students;
				$data = json_encode( $timeslots );
				rename( 'registration.json', 'registration.json.bak' );
				$ret = file_put_contents( 'registration.json', $data );
				if ( $ret === FALSE || strlen( $data ) !== $ret ) {
					$error = 'Unknown error';
				} else {
					$success = true;
				}
			} else {
				$error = 'The time slot is full. Please select another slot.';
			}
		}

		if ( $error !== null ) {
			$success = false;
		}
	}
	
	$hasEmptySlot = false;
	foreach( $timeslots as $time => $registered_groups ) {
		if ( count( $registered_groups ) < 3 ) {
			$hasEmptySlot = true;
			break;
		}
	}

	$ASG = 3;
?>
<!DOCTYPE html>
<html lang="en">
<head>
	<meta charset="utf-8">
	<meta http-equiv="X-UA-Compatible" content="IE=edge">
	<meta name="viewport" content="width=device-width, initial-scale=1">
	<title>CSCI 4180 Assignment <?php echo $ASG; ?>: Demo Registration</title>
	<link href="css/bootstrap.min.css" rel="stylesheet">
	<!--[if lt IE 9]>
	<script src="https://oss.maxcdn.com/html5shiv/3.7.2/html5shiv.min.js"></script>
	<script src="https://oss.maxcdn.com/respond/1.4.2/respond.min.js"></script>
	<![endif]-->
</head>
<body>
	<div class="container-fluid">
		<div class="row">
			<div class="col-sm-12">
				<h3>
					CSCI 4180 Assignment <?php echo $ASG; ?>: Demo Registration<br />
					<small>Venue: SHB 122</small><br />
					<small>Time: 1:00 pm - 5:00 pm, 19 December 2014</small>
				</h3>
<?php if ( isset( $error ) && $error !== null ) { ?>
				<div class="alert alert-danger" role="alert">
					<strong>Error: </strong><?php echo $error; ?>
				</div>
<?php } else if ( isset( $success ) && $success === true ) { ?>
				<div class="alert alert-success" role="alert">
					<strong>Success: </strong>
					The time slot is registered successfully.
				</div>
<?php } ?>
			</div>
		</div>

<?php if ( $hasEmptySlot ) { ?>
		<div class="row">
			<div class="col-sm-12">
				<div class="panel panel-default">
					<div class="panel-heading">
						<h4 class="panel-title">
							Time Slot Registration Form
						</h4>
					</div>
					<div class="panel-body">
						<div class="col-sm-4">
							<h5><strong>Announcement</strong></h6>
							<ul>
								<li>Each group should register a time slot for Assignment <?php echo $ASG; ?> demo.</li>
								<li>Please prepare the login information for accessing your Azure portal.</li>
								<li>If <strong><em>ALL</em></strong> of your group members are not available on the time slots, please contact me at <code>mtyiu@cse.cuhk.edu.hk</code></li>
							</ul>
						</div>
						<div class="col-sm-8">
							<form role="form" class="form-horizontal" method="POST">
								<div class="form-group">
									<label class="col-sm-3 control-label">Student 1</label>
									<div class="col-sm-9">
										<input name="student-0" type="text" class="form-control" placeholder="Please enter the student ID" required />
									</div>
								</div>
								<div class="form-group">
									<label class="col-sm-3 control-label">Student 2</label>
									<div class="col-sm-9">
										<input name="student-1" type="text" class="form-control" placeholder="Please enter the student ID (Optional)" />
									</div>
								</div>
								<div class="form-group">
									<label class="col-sm-3 control-label">Student 3</label>
									<div class="col-sm-9">
										<input name="student-2" type="text" class="form-control" placeholder="Please enter the student ID (Optional)" />
									</div>
								</div>
								<div class="form-group">
									<label class="col-sm-3 control-label">Timeslot</label>
									<div class="col-sm-9">
										<select class="form-control" name="timeslot">
<?php
	foreach( $timeslots as $time => $registered_groups ) {
		if ( count( $registered_groups ) < 3 ) {
?>
											<option value="<?php echo $time; ?>"><?php echo $time; ?></option>
<?php
		}
	}
?>
										</select>
									</div>
								</div>
								<div class="text-center">
									<button type="submit" class="btn btn-primary">Submit</button>
									<button type="reset" class="btn btn-danger">Reset</button>
								</div>
							</form>
						</div>
					</div>
				</div>
			</div>
		</div>
<?php } ?>

		<div class="row">
			<div class="col-sm-12">
				<table class="table table-striped table-hover">
					<thead>
						<th width="25%">Time</th>
						<th width="25%">Slot 1</th>
						<th width="25%">Slot 2</th>
						<th width="25%">Slot 3</th>
					</thead>
					<tbody>
<?php
	$no_of_groups_registered = 0;
	foreach( $timeslots as $t => $students ) {
?>
						<tr>
							<td><?php echo $t; ?></td>
<?php
		$count = 0;
		foreach( $students as $s ) {
?>
							<td><?php echo implode( '<br />', $s ); ?></td>
<?php
			$count++;
			$no_of_groups_registered++;
		}

		for( ; $count < 3; $count++ ) {
?>
							<td><span class="text-muted">Available</span></td>
<?php
		}
?>
						</tr>
<?php
	}
?>
						<tr>
							<td colspan="4" align="right">
								<strong>
									No. of groups registered:
									<span class="badge">
										<?php echo $no_of_groups_registered; ?>
									</span>; 
									Remaining slots:
									<span class="badge">
										<?php echo 24 - $no_of_groups_registered; ?>
									</span>
								</strong>
							</td>
						</tr>
					</tbody>
				</table>
			</div>
		</div>
	</div>

	<script src="https://ajax.googleapis.com/ajax/libs/jquery/1.11.1/jquery.min.js"></script>
	<script src="js/bootstrap.min.js"></script>
</body>
</html>
