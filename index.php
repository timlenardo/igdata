<?php
    session_start();
?>


<head>
  <link rel="stylesheet" href="style.css">
  <meta content="width=device-width, initial-scale=1" name="viewport" />
  <script src="https://cdnjs.cloudflare.com/ajax/libs/Chart.js/2.7.2/Chart.bundle.js"> </script>
</head>

<html>
  <body>
    <div id="container">
      <h1> How did Instagram's algorithmic feed affect your account? </h1>

      <?php
          // This is where we need to build it!
          if ($_SESSION['logged_in'] == 1) {
              $username = $_SESSION['username'];

              // echo "<h1>".$username."</h1>";

              $db_servername = "localhost";
              $db_username = "scraping";
              $db_password = "7DusDaCRVwztxjZFmecEPaQk";
              $db_tablename = "igdata";

              $conn = new mysqli($db_servername, $db_username, $db_password, $db_tablename);
              if ($conn->connect_error) {
                  echo "<h1>".$conn->connect_error."</h1>";
                  die("Connection failed: " . $conn->connect_error);
              }

              $data_query = "SELECT (sum(num_likes) / count(*)) as average, MONTH(timestamp) as month FROM posts WHERE username='".$username."' AND timestamp>='2016-02-01' AND timestamp <= '2016-12-31' group by 2 order by 2 desc";
              $result = $conn->query($data_query);

              $averages = array();


              if ($result->num_rows > 0) {
                  // output data of each row
                  $before_average = 0.0;
                  $after_average = 0.0;
                  while($row = $result->fetch_assoc()) {
                      $average = (float)$row["average"];
                      $month = (int)$row["month"];
                      if ($month <= 6) {
                        $before_average += $average;
                      } else {
                        $after_average += $average;
                      }
                      array_push($averages, $average);
                  }
                  $before_average = $before_average / 4.0;
                  $after_average = $after_average / 4.0;
                  $increase_percentage =  (int)(($after_average / $before_average - 1.0) * 100.0);

                  $changed_text = "<span class=\"increased\">stayed the same </span>";
                  if ($increase_percentage > 0) {
                    $changed_text = "<span class=\"increased\">increased by ".$increase_percentage."% </span>";
                  } else if ($increase_percentage < 0) {
                    $changed_text = "<span class=\"decreased\">decreased by ".$increase_percentage."% </span>";
                  }

                  echo "<h2> Your average number of likes per photo ".$changed_text." after algorithmic feed launched in July 2016: </h2>";
                  echo "<canvas id=\"chart\" width=\"375\" height=\"375\"> </canvas>";


                  echo "
                    <script>
                      var originalLineDraw = Chart.controllers.line.prototype.draw;
                      Chart.helpers.extend(Chart.controllers.line.prototype, {
                      draw: function() {
                        originalLineDraw.apply(this, arguments);

                        var chart = this.chart;
                        var ctx = chart.chart.ctx;

                        var index = chart.config.data.lineAtIndex;
                        if (index) {
                          var xaxis = chart.scales['x-axis-0'];
                          var yaxis = chart.scales['y-axis-0'];

                          ctx.save();
                          ctx.beginPath();
                          ctx.moveTo(xaxis.getPixelForValue(undefined, index), yaxis.top);
                          ctx.strokeStyle = '#ff00007D';
                          ctx.lineWidth=20;
                          ctx.lineTo(xaxis.getPixelForValue(undefined, index), yaxis.bottom);
                          ctx.stroke();
                          ctx.restore();
                        }
                      }
                      });
                      var config = {
                        type: 'line',
                        data: {
                          labels: ['February', 'March', 'April', 'May', 'June', 'July', 'August', 'September', 'October', 'November', 'December'],
                          datasets: [{
                                 label: 'Likes Per Photo',
                                 backgroundColor: \"#00FF6F\",
                                 borderColor: \"#00FF6F\",
                                 data: [
                                    ".$averages[10].",
                                    ".$averages[9].",
                                    ".$averages[8].",
                                    ".$averages[7].",
                        						".$averages[6].",
                                    ".$averages[5].",
                                    ".$averages[4].",
                                    ".$averages[3].",
                                    ".$averages[2].",
                                    ".$averages[1].",
                                    ".$averages[0]."
                                 ],
                                 fill: false,
                          }],
                          lineAtIndex: 5
                        },
                      	options: {
                      		responsive: true,
                      		title: {
                      			display: true,
                      			text: 'Chart.js Line Chart'
                      		},
                          layout: {
                            padding: {
                              left: 10,
                              right: 20,
                              top: 10,
                              bottom: 10
                            }
                          },
                      		tooltips: {
                      			mode: 'index',
                      			intersect: false,
                      		},
                      		hover: {
                      			mode: 'nearest',
                      			intersect: true
                      		},
                      		scales: {
                      			xAxes: [{
                      				display: true,
                      				scaleLabel: {
                					       display: true,
                  					     labelString: 'Month'
                  				    }
                        		}],
                        		yAxes: [{
                        			display: true,
                        			scaleLabel: {
                        				display: true,
                        				labelString: 'Likes per post'
                        			}
                        		}]
                        	}
                        }
                      };

                        window.onload = function() {
                          console.log(\"Here as\");
                          var ctx = document.getElementById(\"chart\").getContext('2d');
                          var myChart = new Chart(ctx, config);
                        };

                    </script>

                    ";
              }

              echo "
                <form action=\"logout.php\" method=\"post\">
                  <input id=\"logout\" type=\"submit\" value=\"Logout\"></input>
                </form>";

          } else {
              echo "
                <h2> Log in to find out: <h2>
                <form action=\"login.php\" method=\"post\">
                  <input type=\"text\" name=\"username\" placeholder=\"username\"><br>
                  <input type=\"password\" name=\"password\" placeholder=\"password\"><br>
                  <input id=\"submit\" type=\"submit\">
                </form>";
          }
      ?>
    </div>
  </body>
</html>
