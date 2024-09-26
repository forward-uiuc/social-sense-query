FROM python:3

WORKDIR /usr/src/app

RUN pip install numpy
RUN pip install Pillow
RUN python -m pip install -U matplotlib

COPY . .

CMD ["python", "python.py"]
