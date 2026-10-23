# add/resphy


## GLI lung function calculator
Must install R and rspiro R library in order to use GliLungFunction component.

```bash
sudo apt-get install r-base r-base-dev
R
```

```R
install.packages("glue")
install.packages("usethis")
install.packages("devtools")

#install the package in a specific location
withr::with_libpaths(
    new="/local/rpackages",
    devtools::install_github("thlytras/rspiro"
)

# example usage to check
#' @param age Age in years
#' @param height Height in meters
#' @param gender Gender (1 = male, 2 = female) or a factor with two levels (first = male). Default is 1.
#' @param ethnicity Ethnicity (1 = Caucasian, 2 = African-American, 3 = NE Asian, 4 = SE Asian,
#' 5 = Other/mixed). Default is 1.
#' @param param A character vector, containing one of more of the following parameters (case insensitive):
#' "FEV1", "FVC", "FEV1FVC", "FEF2575", "FEF75", "FEV075", "FEV075FVC"

rspiro::pred_GLI(56, 1.7,1,1,param="FEV1")
```

Set the config setting to point to the R packages:
```ini
[Resphy]
rspiro_path=/local/rpackages
```

Then use ajax-func=GliLungFunction in field settings.
