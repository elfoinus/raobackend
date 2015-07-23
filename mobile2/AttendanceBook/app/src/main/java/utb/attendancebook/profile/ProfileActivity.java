package utb.attendancebook.profile;
import android.app.ProgressDialog;
import android.content.SharedPreferences;
import android.os.AsyncTask;
import android.os.Bundle;
import android.support.v4.app.NavUtils;
import android.support.v7.app.ActionBarActivity;
import android.support.v7.widget.Toolbar;
import utb.attendancebook.aidClasses.WordManagement;
import android.util.Log;
import android.view.Menu;
import android.view.MenuItem;
import android.widget.TextView;
import org.json.JSONException;
import org.json.JSONObject;
import java.io.BufferedReader;
import java.io.InputStream;
import java.io.InputStreamReader;
import java.net.HttpURLConnection;
import java.net.URL;
import utb.attendancebook.R;
import utb.attendancebook.teachers.TeacherItem;

/**
 * Created by daniela on 29/05/15.
 */
public class ProfileActivity extends ActionBarActivity {

    private static final String TAG = "ProfileActivity: ";
    private TeacherItem mTeacher = new TeacherItem();
    private ProgressDialog pd = null;


    @Override
    protected void onCreate(Bundle savedInstanceState) {
        super.onCreate(savedInstanceState);
        setContentView(R.layout.activity_profile);

        //Set the new toolbar to show up here too.
        Toolbar toolbar = (Toolbar) findViewById(R.id.app_bar);
        setSupportActionBar(toolbar);

        //Adding the back button, this needs to be handled in the onOptionsItemSelected
        getSupportActionBar().setHomeButtonEnabled(true);
        getSupportActionBar().setDisplayHomeAsUpEnabled(true);

        /*Downloading data from below url*/
        SharedPreferences settings = getSharedPreferences("TokenStorage", 0);
        String id = settings.getString("id", "");
        final String url = "http://104.236.31.197/teacher/"+id+"?username="+id+"&token="+settings.getString("token","");

        /*New spinner*/
        this.pd = ProgressDialog.show(this, getResources().getText(R.string.process_dialog_title), getResources().getText(R.string.process_dialog_text), true, false);

        /*New Async task*/
        new AsyncHttpTask().execute(url);
    }

    public void inflateTeacherInfo(){
        TextView name = (TextView) findViewById(R.id.teacher_name);
        TextView id = (TextView) findViewById(R.id.teacher_id);
        TextView type = (TextView) findViewById(R.id.teacher_type);
        TextView school = (TextView) findViewById(R.id.teacher_school);
        TextView department = (TextView) findViewById(R.id.teacher_department);
        TextView email = (TextView) findViewById(R.id.teacher_email);
        name.setText(mTeacher.getTeacherName());
        id.setText(mTeacher.getId());
        type.setText("Profesor de " + mTeacher.getTeacherType()+". Facultad de "+ mTeacher.getTeacherSchool()+". Departamento: "+mTeacher.getTeacherDepartment());
        school.setText(mTeacher.getTeacherSchool());
        department.setText(mTeacher.getTeacherDepartment());
        email.setText(mTeacher.getTeacherEmail());
    }

    @Override
    public boolean onCreateOptionsMenu(Menu menu) {
        // Inflate the menu; this adds items to the action bar if it is present.
        getMenuInflater().inflate(R.menu.menu_profile, menu);
        return true;
    }

    @Override
    public boolean onOptionsItemSelected(MenuItem item) {
        int id = item.getItemId();
        if(id == R.id.action_settings){
            return true;
        }
        if(id == R.id.home){
            NavUtils.navigateUpFromSameTask(this);
        }
        return super.onOptionsItemSelected(item);
    }

    private void parseResult(String result) {
        try {
            JSONObject response = new JSONObject(result);
            String names = response.optString("names");
            String lastnames = response.optString("lastnames");
            mTeacher.setTeacherName(names,lastnames);
            mTeacher.setId(response.optString("id"));
            mTeacher.setTeacherType(response.optString("type"));
            mTeacher.setTeacherSchool(response.optString("school"));
            mTeacher.setTeacherDepartment(response.optString("department"));
            mTeacher.setTeacherEmail(response.optString("email"));

            JSONObject posts = new JSONObject(response.optString("links"));
            mTeacher.setUri(posts.optString("attendance_uri"));

        } catch (JSONException e) {
            e.printStackTrace();
        }
    }

    public class AsyncHttpTask extends AsyncTask<String, Void, Integer> {

        @Override
        protected Integer doInBackground(String... params) {
            InputStream inputStream = null;
            Integer result = 0;
            HttpURLConnection urlConnection = null;

            try {
                /* forming th java.net.URL object */
                URL url = new URL(params[0]);
                urlConnection = (HttpURLConnection) url.openConnection();

                /* for Get request */
                urlConnection.setRequestMethod("GET");
                int statusCode = urlConnection.getResponseCode();

                /* 200 represents HTTP OK */
                if (statusCode == 200) {
                    BufferedReader r = new BufferedReader(new InputStreamReader(urlConnection.getInputStream()));
                    StringBuilder response = new StringBuilder();
                    String line;
                    while ((line = r.readLine()) != null) {
                        response.append(line);
                    }
                    parseResult(response.toString());
                    result = 1; // Successful
                } else {
                    result = 0; //"Failed to fetch data!";
                }

            } catch (Exception e) {
                Log.d(TAG, e.getLocalizedMessage());
            }
            return result; //"Failed to fetch data!";
        }

        @Override
        protected void onPostExecute(Integer result) {
            /* Download complete. Lets update UI */
            if (result == 1) {
                inflateTeacherInfo();
            } else {
                Log.e(TAG, "Failed to fetch data!");
            }

            if (ProfileActivity.this.pd != null) {
                ProfileActivity.this.pd.dismiss();
            }
        }
    }

}