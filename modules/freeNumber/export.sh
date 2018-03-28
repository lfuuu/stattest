../../yii freeNumber/export/beauty-level | gzip > ../../web/export/free-number/beauty-level.tsv.gz;
../../yii freeNumber/export/city | gzip > ../../web/export/free-number/city.tsv.gz;
../../yii freeNumber/export/country | gzip > ../../web/export/free-number/country.tsv.gz;
../../yii freeNumber/export/ndc-type | gzip > ../../web/export/free-number/ndc-type.tsv.gz;
../../yii freeNumber/export/number | gzip > ../../web/export/free-number/number_tmp.tsv.gz;
rm ../../web/export/free-number/number.tsv.gz;
mv ../../web/export/free-number/number_tmp.tsv.gz ../../web/export/free-number/number.tsv.gz;
