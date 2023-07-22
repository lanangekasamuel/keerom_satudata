<?php
$TemplatWaras1 = TemplatWaras1::init();

?>

<?php $TemplatWaras1->open('content') ?>
<div class='mb-10'>
	<a href='<?= ROOT_URL ?>giadmin/instansi/form.htm' class='btn btn-flat btn-success' data-toggle='tooltip' data-placement='top' title='Tambah Instansi'>
		<i class='fa fa-plus'></i> &nbsp; Tambah Instansi
	</a>
</div>

<table id='table_instansi' class ='table table-condensed table-bordered table-striped' border='0' cellpadding='0' cellspacing='0' width='100%'>
	<thead>
		  <tr>
		  	<th>No</th>
		  	<th>Nama Instansi</th>
		  	<th>Singkatan</th>
		  	<th>Kategori</th>
		  	<th>Urusan</th>
		  	<th>Suburusan</th>
		  	<th>Users</th>
		  	<th>action</th>
		  </tr>
	</thead>
	<tbody>
		<?php $no = 0;foreach ($qskpdq->result_array() as $rInstansi): $no++; ?>
			<tr>
				<td><?= $no ?></td>
				<td><?= $rInstansi['nama_instansi'] ?></td>
				<td><?= $rInstansi['singkatan'] ?></td>
				<td><?= $rInstansi['kategori_instansi'] ?></td>
				<td><?= $rInstansi['urai_urusan'] ?></td>
				<td><?= $rInstansi['urai_suburusan'] ?></td>
				<td>
					<?php if(empty($rInstansi['userslist'])): ?>
						<i class="text-red">Kosong</i>
						<a href="<?= ROOT_URL ?>giadmin/user/form.htm?idinstansi=?<?= $rInstansi['idinstansi'] ?>">(tambah)</a>
					<?php else: echo $rInstansi['userslist']; endif; ?>
				</td>
				<td nowrap>
					<a href="<?= ROOT_URL ?>giadmin/instansi/<?= $rInstansi['idinstansi'] ?>/form.htm" class="btn btn-primary" data-toggle="tooltip" data-placement="top" title="Edit">
						<i class="fa fa-edit"></i>
					</a>
					<a hidden href="javascript:codel('<?= ROOT_URL ?>giadmin/instansi/<?= $rInstansi['idinstansi'] ?>/del.htm');" class="btn btn-danger" data-toggle="tooltip" data-placement="top" title="Delete">
						<i class="fa fa-times-circle"></i>
					</a>
				</td>
			</tr>
		<?php endforeach ?>
	</tbody>
</table>
<?php $TemplatWaras1->close() ?>

<?php $TemplatWaras1->open('pgScript') ?>
<script src="{themepath}js/skpd.js"></script>
<script src="{themepath}js/instansi.js?7"></script>
<?php $TemplatWaras1->close() ?>
