<div class="container-fluid">
  <div class="row mb-2">
    <div class="col-sm-6">
      <h1 class="m-0 text-dark" style="padding: 20px 0 0 0;">re_stat</h1>
    </div><!-- /.col -->
    <div class="col-sm-6">
      <ol class="breadcrumb float-sm-right">
        <li class="breadcrumb-item"><a href="admin.php"><i class="fa fa-home"></i></a></li>
        <li class="breadcrumb-item"><a href="admin.php?mod=extras">{l_extras}</a></li>
        <li class="breadcrumb-item active" aria-current="page">re_stat</li>
      </ol>
    </div><!-- /.col -->
  </div><!-- /.row -->
</div><!-- /.container-fluid -->
<div class="card plugin-settings-card">
  <h5 class="card-header d-flex justify-content-between align-items-center" data-plugin-settings-header>
    <span>re_stat [add]{l_re_stat:add}[/add][edit]{l_re_stat:edit}[/edit] {l_re_stat:list_item}</span>{documentation_button}
  </h5>
  <div class="card-body plugin-settings-content">
    <div class="container-fluid">
      <nav class="mb-3" aria-label="{l_re_stat:actions_aria}">
        <div class="nav nav-tabs">
          <a class="nav-item nav-link" href="admin.php?mod=extra-config&amp;plugin=re_stat">{l_re_stat:list}</a>
          <a class="nav-item nav-link active" href="admin.php?mod=extra-config&amp;plugin=re_stat&amp;action=add&amp;id=-1" aria-current="page">{form_tab}</a>
        </div>
      </nav>
      <form method="post" action="admin.php?mod=extra-config&amp;plugin=re_stat">
        <table class="table table-sm table-hover ">
          <thead>
            <tr>
              <td>{l_re_stat:code}</td>
              <td><input type="text" size="40" name="code" value="{code}"/></td>
            </tr>
            <tr>
              <td>{l_re_stat:static_page}</td>
              <td>{statlist}</td>
            </tr>
        </table>
        <div class="card-footer text-center">
          <input
            type="submit"
            value="[add]{l_re_stat:add}[/add][edit]{l_re_stat:edit}[/edit] {l_re_stat:list_item}"
            class="btn btn-outline-success"
          />
        </div>
        <input type="hidden" name="action" value="confirm"/>
        <input type="hidden" name="id" value="{id}"/>
      </form>
    </div>
  </div>
</div>
