#Kickstart Template Builder (KTB)

KTB is a templating system written in php use to deploy consistent kickstart
images using a base kickstart image with variables that can change based
on the site or version and architecture you are build to.

By using a lookup mechanism that searches from the most explicit
entry to the least explicit entry, you can choose the correct
variable based on the input to the script.

For example, you could have SITEA for RHEL 5 Given back one OPTION
for a KEY in the file, and SITEA for RHEL 6 give back a different key.

#Calling the script
you pass one http get variable to the script, build, which is a CSV list
of GROUP,SITE,MAJOR,MINOR,ARCH ; and it will output a kickstart file.   

#File Layout
fragments: this file holds all the key/value pairs that control how
the templates are filled out.

fragments entries are defined as: KEY|GROUP,SITE,MAJOR,MINOR,ARCH|Value
where:
    KEY   = is the lookup key you place in the template between ##KEY##
    GROUP = is the group that this machine would belong to (server/desktop)
    SITE  = the site this machine is located at
    MAJOR = the major version of the OS
    MINOR = the minor version of the OS
    ARCH  = the arch (x8664 or i686) for the OS
    VALUE = the value to return for the key, @name  would load the file name.

GROUP,SITE,MAJOR,MINOR, and ARCH can all be replace with * which means
that any value would work there.  If you have a key that matches
multiple values, then it will pick the entry that is MOST specific (The
entry that has the least amount of *'s)

fragmentData/ : If a value for a key is larger than a line, then you can
place the data in the fragmentData directory and call to it by
place a @ before the name, so @fragmentData/packageList  would load
that file as the value for the given key.

templates/:  This directory holds the kickstart template files. templates
are chosen from the KSTEMPLATE key within the fragments file.


#Sample Usage

* to add a package to the 64 bit RHEL5.9 build you would edit:
fragmentData/packageList.rhel5.full.x8664  and add the package to the list
(do note, that if other templates are sharing this file, which they are,
then that means all RHEL5 64bit builds will get this update.)

* to modify the partition layout for RHEL6 machines you would edit:
fragmentData/paritionList.rhel6.full  and just modify the partition layout.

* If SITEA needed it's own package list, you would edit the fragments file
and add a line: PACKAGES|*,SITEA,6,*,i686| @fragmentData/packages.SITEA.i686

* if SITEA needed it's own package list for only DESKTOP machines you would edit
the fragments file and add the line:
PACKAGES|DESKTOP,SITEA,6,*,i686| @fragmentData/packages.SITEA.i686

* if SITEA needed it's own package list for only DESKTOP machines at version 4,
you would edit the fragments file and add the line:
PACKAGES|DESKTOP,SITEA,6,4,i686| @fragmentData/packages.SITEA.i686

* if MACHINEA  needs a special parition layout, you would edit the fragments file
and add the line:
PARTITION|MACHINEA,*,6,*,*| @fragmentData/paritionList.MACHINEA
and then create the file:
fragmentData/paritionList.MACHINEA
with the partion data
