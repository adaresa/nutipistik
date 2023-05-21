# Nutipistik

Nutipistik is an innovative smart plug solution designed and built entirely by me as a solo project. The motivation behind this project was my interest in promoting smarter and cost-efficient electricity usage. The project incorporates a multi-component architecture, utilizing various technologies and languages, including PHP, Python, CSS, C++, and JavaScript.

## Table of Contents

- [Components](#components)
  - [Analysis](#analysis)
  - [AWS](#aws)
  - [Hardware](#hardware)
  - [Webapp](#webapp)
- [Contributing](#contributing)

## Components

### Analysis

The Analysis component houses a Python script that serves as Nutipistik's financial advisor. It simulates and compares the total cost of three different electricity pricing strategies over a 12-month period, namely, a fixed electricity price, the daily average market price, and the 'Cheapest Hours' market price strategy. It then visualizes the resulting data on a line graph, providing a clear representation of cost changes over time.

### AWS

The AWS component comprises scripts hosted on Amazon Web Services. It undertakes the essential task of keeping the webapp's database updated with the latest electricity prices. In addition, it determines the appropriate state of the smart plugs based on their selected control mode and user-defined parameters.

### Hardware

The Hardware component encompasses all physical aspects of the Nutipistik project. It includes Case and PCB designs, 3D models of the complete hardware solution, as well as the Firmware running on the ESP12E microcontroller embedded in the PCB.

### Webapp

The Webapp component is the user interface of the Nutipistik project. It provides an interactive platform for users to control the smart plug system effectively. 

## Contributing

As a solo developer, I welcome any contributions! Whether you have feature requests, bug fixes, or any other improvements, please feel free to submit a pull request or open an issue. Your help in making Nutipistik better is greatly appreciated!
